## Fraud Compare – Technical Notes

This directory powers the standalone “Quiz Fraud Compare” page for classic self-paced quizzes. It compares two students directly, and it can scan all student pairs in one quiz for suspicious timing similarity.

The page is implemented in [index.php](/Users/maxbisschop/dev/www/max-quiz/web/fraud-compare/index.php) and reads the main quiz schema directly through `config/db.php`.

### Files At A Glance

- `index.php` – Standalone entry point, data loading, pair analysis, baseline calculations, permutation-based p-values, and HTML rendering.
- `README.md` – Technical explanation of the metrics, formulas, and interpretation notes used by the page.

### Data Sources

The page uses the classic quiz tables:

- `submission`
  - one row per quiz attempt
  - relevant fields: `id`, `student_nr`, `first_name`, `last_name`, `class`, `start_time`, `end_time`, `last_updated`, `no_questions`, `no_answered`, `no_correct`, `quiz_id`
- `log`
  - one row per answered question
  - relevant fields: `submission_id`, `question_id`, `answer_no`, `correct`, `no_answered`, `timestamp`
- `question`
  - used only for question labels and preview text in the direct compare view

### Selection Rules

For a given `quiz_id`, the page selects the latest submission per student:

- source: `submission`
- ordering rule: `COALESCE(end_time, last_updated, start_time) DESC, id DESC`
- one submission is kept per `student_nr`

All pair metrics on the page are based on those latest submissions only.

### What Is Measured

These values come directly from stored quiz data or simple row matching:

1. **Matched questions**
   - Definition: questions where both selected students have a `log` row for the same `question_id`
   - Meaning: the common comparison set for that pair

2. **Close row**
   - Definition: a matched question where
     - `abs(timestamp_a - timestamp_b) <= SUSPICIOUS_SECONDS`
   - Current constant:
     - `SUSPICIOUS_SECONDS = 15`

3. **Diff seconds**
   - Definition:
     - `diff(q) = |t_a(q) - t_b(q)|`
   - where `t_a(q)` and `t_b(q)` are the stored answer timestamps for the same matched question

4. **Per-question answer time**
   - Used in the quiz timing stats section
   - For one submission:
     - first answered question: `timestamp(first answer) - start_time`
     - later answered question: `timestamp(current answer) - timestamp(previous answer)`

### Pair Metrics

For one pair of students inside one quiz:

1. **Matched Questions**
   - Count of matched questions
   - Formula:
     - `n = count(matched questions)`

2. **Close Rows**
   - Count of matched questions answered within the configured close window
   - Formula:
     - `k = count(diff(q) <= 15)`

3. **Close %**
   - Fraction of matched questions that are close
   - Formula:
     - `close_ratio = k / n`

4. **Longest Close Run**
   - Length of the longest uninterrupted sequence of matched questions where every row is close
   - This distinguishes scattered close answers from sustained synchronized runs

5. **Dominant Leader**
   - Among close rows, which student is more often first
   - Exact timestamp ties are treated separately and do not count toward a leader

6. **Same-Leader Close %**
   - Fraction of non-tied close rows where the dominant leader is first
   - Formula:
     - `same_leader_close_ratio = dominant_close_lead_count / close_rows_with_leader`
   - Interpretation:
     - values near `100%` mean the same student is almost always ahead on close rows
     - values near `50%` mean the lead alternates more often

7. **Longest Same-Leader Run**
   - Length of the longest uninterrupted close-answer run where the same student stays ahead

8. **Smallest Diff**
   - Minimum `diff(q)` among matched questions

9. **Average Diff**
   - Mean of `diff(q)` across matched questions
   - Formula:
     - `avg_diff = sum(diff(q)) / n`

10. **Expected Close %**
   - Quiz-wide baseline close rate used as the expected proportion for one matched row
   - Formula:
     - `expected_close_ratio = baseline_close / baseline_matched`

11. **Observed Close %**
   - Same as `Close %`
   - Formula:
     - `observed_close_ratio = close_ratio = k / n`

### Quiz-Level Timing Stats

The `Timing Stats` panel in the all-pairs view summarizes per-question answer times over all latest submissions in the quiz.

1. **Answered Questions**
   - Number of answer-interval samples used

2. **Average Answer Time**
   - Mean of all per-question answer intervals

3. **Std Dev Answer Time**
   - Sample standard deviation of those intervals
   - Formula:
     - `stdev = sqrt(sum((x_i - mean)^2) / (m - 1))`
   - where `m` is the number of interval samples

4. **Average Seconds**
   - Same mean as above, displayed in raw seconds

### Quiz Baseline

The `Quiz Baseline` section estimates how common “close answers” are in the selected quiz population.

For every unique pair of students in the quiz:

- compute the pair summary
- accumulate:
  - total matched answer pairs
  - total close answer pairs

Definitions:

- `baseline_matched = sum(n_pair over all pairs with n_pair > 0)`
- `baseline_close = sum(k_pair over all pairs with n_pair > 0)`

Then:

- `P(within 15s) = baseline_close / baseline_matched`

This is an empirical quiz-local baseline, not a universal probability.

### Potential Fraud Couples Filter

The all-pairs view lists a pair when:

- `matched_questions > 0`
- and `close_ratio > selected_threshold`

The threshold is controlled by a dropdown on the page:

- `10%`
- `25%`
- `50%`
- `75%`
- `90%`

Default:

- `50%`

### Rarity 1:N

`Rarity 1:N` is a model-based rarity score derived from the quiz baseline.

For a pair with:

- `n = matched_questions`
- `k = close_rows`
- `p = P(within 15s)` from the quiz baseline

the page computes a binomial tail probability:

- `P(X >= k)` where `X ~ Binomial(n, p)`

Expanded:

- `P(X >= k) = sum from i = k to n of C(n, i) * p^i * (1 - p)^(n - i)`

The displayed rarity is the reciprocal:

- `1 / P(X >= k)`

Formatting:

- small reciprocal values: regular integer format, e.g. `1:42`
- large reciprocal values: rounded scientific notation, e.g. `1:3E+13`

Important interpretation:

- this is a baseline-model rarity score
- it is not proof of fraud
- it is not the probability that the pair cheated
- it is not the probability that the pair is innocent

Main limitation:

- the binomial model assumes equal per-question probability and independence
- quiz timing often has drift and dependence between questions
- therefore this metric is best treated as a heuristic screening score

### Permutation p-value

`Perm. p` is a permutation-style empirical p-value based on random student reshuffles inside the same quiz.

Current configuration:

- `PERMUTATION_RUNS = 100000`

Procedure:

1. Build the real pair summaries for every unique student pair in the quiz.
2. Create many random one-to-one reshuffles of the student list with no student matched to themselves.
3. For each reshuffle:
   - look up the already computed pair summary for the resulting pairs
   - collect those reshuffled pair summaries into a null distribution
4. For the selected or listed pair, count how many reshuffled scores are at least as extreme as the observed pair on this ordering:
   - higher `Observed Close %`
   - then higher `Same-Leader Close %`
   - then higher `Longest Same-Leader Run`
   - then higher `Longest Close Run`
   - then higher `Close Rows`

The page uses the Monte Carlo p-value with a +1 correction:

- `p_perm = (1 + #null_scores >= observed_score) / (1 + total_null_scores)`

Interpretation:

- smaller p-value: the pair is more unusual compared with random pairings from the same quiz
- larger p-value: the pair is not very unusual under the reshuffled null model

Important interpretation:

- this is not `P(fraud | data)`
- it is not a legal or disciplinary probability of guilt
- it is a rarity-under-random-pairing measure

### Why `Perm. p` And `Rarity 1:N` Can Differ

They come from different null models:

- `Perm. p`
  - empirical, based on random reshuffles inside the observed quiz population
- `Rarity 1:N`
  - theoretical binomial model using the quiz-wide close baseline

Because of drift, question dependence, and real quiz structure, `Perm. p` is often the more defensible metric of the two.

### Direct Compare View

The direct compare view shows:

- selected submissions
- pair summary
- per-question comparison
- bottom `Fraud Signals` summary

The bottom `Fraud Signals` section repeats the overview-style indicators for the selected pair:

- matched questions
- close rows
- expected close %
- observed close %
- longest close run
- dominant leader
- same-leader close %
- longest same-leader run
- permutation p-value
- rarity 1:N
- smallest diff
- average diff

This lets the user inspect both:

- the detailed question-by-question evidence
- the pair-level summary metrics

### What Is Calculated Versus What Is Inferred

Direct calculations:

- matched question count
- close row count
- observed close %
- expected close %
- longest close run
- dominant leader
- same-leader close %
- longest same-leader run
- smallest diff
- average diff
- quiz baseline `P(within 15s)`
- binomial tail probability
- reciprocal rarity score
- permutation p-value

Interpretive outputs:

- “Potential Fraud Couple” label
- rarity wording
- p-value wording

These are screening aids only. They prioritize manual review; they do not establish proof of fraud.

### Practical Caveats

- Results depend on the latest submission per student, not all attempts.
- Timing-only evidence can overstate certainty when quiz pace drift accumulates across questions.
- `Perm. p` is Monte Carlo based and can vary if the random seed is not fixed.
- `Rarity 1:N` can become very large because the binomial model is stricter than real quiz behavior.
- Neither metric should be presented as a final probability of cheating.

### Suggested Safe Interpretation

- `Observed Close %` tells you how synchronized the pair was overall.
- `Longest Close Run` tells you whether the closeness was sustained in one uninterrupted block rather than scattered across the quiz.
- `Same-Leader Close %` tells you whether the same student keeps staying slightly ahead when rows are close.
- `Longest Same-Leader Run` tells you whether that directionality is sustained across a block of close answers.
- `Perm. p` tells you how unusual that synchronization and direction pattern is under random pairings within the same quiz.
- `Rarity 1:N` tells you how unusual it is under the simpler timing baseline model.
- The pair should be treated as:
  - a review candidate
  - not an automatically proven fraud case

This README is intended to make the fraud page metrics reproducible and explainable for future maintenance.

# AI Answer Generation - Visual Guide

## What You'll See

### 1. Question Create Page - Before
When you navigate to create a new question, you'll see:

```
┌─────────────────────────────────────────────────┐
│  📝 Update Question                             │
│  Question ID: (new)                             │
├─────────────────────────────────────────────────┤
│                                                 │
│  ❓ Question                                    │
│  ┌─────────────────────────────────────────┐  │
│  │ Enter your question here...              │  │
│  │                                          │  │
│  │                                          │  │
│  └─────────────────────────────────────────┘  │
│  0 characters                                   │
│                                                 │
│  ┌─────────────────────────────────────────┐  │
│  │   🤖 Generate Answers with AI            │  │ ← NEW BUTTON
│  └─────────────────────────────────────────┘  │
│  ℹ️ Enter your question above, then click     │
│  this button to automatically generate 6       │
│  answers using AI (answer 1 will be correct)   │
│                                                 │
├─────────────────────────────────────────────────┤
│  📝 Answer Options                              │
│  ┌───────────────┐  ┌───────────────┐         │
│  │ Answer #1     │  │ Answer #2     │         │
│  │               │  │               │         │
│  └───────────────┘  └───────────────┘         │
│  ... (6 answer fields)                         │
└─────────────────────────────────────────────────┘
```

### 2. Example Usage

#### Step 1: Enter Your Question
```
┌─────────────────────────────────────────────┐
│ ❓ Question                                  │
│ ┌───────────────────────────────────────┐  │
│ │ What does HTML stand for?             │  │
│ └───────────────────────────────────────┘  │
│ 29 characters                               │
└─────────────────────────────────────────────┘
```

#### Step 2: Click the AI Button
```
┌──────────────────────────────────────────┐
│  ⏳ Generating answers...                │  ← Loading state
└──────────────────────────────────────────┘
```

#### Step 3: AI Fills in Answers Automatically
```
┌─────────────────────────────────────────────────┐
│ 📝 Answer Options                               │
├─────────────────────────────────────────────────┤
│ ┌────────────────────┐  ┌────────────────────┐ │
│ │ Answer #1       ✓  │  │ Answer #2          │ │
│ │ HyperText Markup   │  │ Hyper Transfer     │ │ ← Correct (Green)
│ │ Language           │  │ Markup Language    │ │
│ └────────────────────┘  └────────────────────┘ │
│                                                 │
│ ┌────────────────────┐  ┌────────────────────┐ │
│ │ Answer #3          │  │ Answer #4          │ │
│ │ High-Level Text    │  │ Home Tool Markup   │ │
│ │ Modeling Language  │  │ Language           │ │
│ └────────────────────┘  └────────────────────┘ │
│                                                 │
│ ┌────────────────────┐  ┌────────────────────┐ │
│ │ Answer #5          │  │ Answer #6          │ │
│ │ HyperText Machine  │  │ Hyperlink and Text │ │
│ │ Language           │  │ Markup Language    │ │
│ └────────────────────┘  └────────────────────┘ │
└─────────────────────────────────────────────────┘
```

#### Step 4: Correct Answer Field Auto-Set
```
┌─────────────────────────────────────────────┐
│ ⚙️ Settings                                  │
├─────────────────────────────────────────────┤
│ Correct Answer:  [1] ✓                      │  ← Automatically set to 1
│ Category Label:  [           ]              │
└─────────────────────────────────────────────┘
```

### 3. Loading States

#### Before Click
```
┌──────────────────────────────────────┐
│  🤖 Generate Answers with AI         │
└──────────────────────────────────────┘
```

#### During Generation
```
┌──────────────────────────────────────┐
│  ⏳ ⟲ Generating answers...          │  (Button disabled)
└──────────────────────────────────────┘
```

#### After Success
```
┌──────────────────────────────────────┐
│  🤖 Generate Answers with AI         │  (Button enabled again)
└──────────────────────────────────────┘

[✅ Answers generated successfully! The first answer is marked as correct.]
```

### 4. Error States

#### No Question Entered
```
[⚠️ Please enter a question first before generating answers.]
```

#### LM Studio Not Running
```
[❌ Error connecting to AI service. Please make sure LM Studio is running on port 1234.]
```

#### Invalid AI Response
```
[❌ Error: AI did not return 6 answers. Please try again.]
```

### 5. Complete Workflow Example

**Question:** "Which CSS property is used to change text color?"

**AI Generated Answers:**
1. ✅ `color` (CORRECT - Green highlighted)
2. ❌ `text-color`
3. ❌ `font-color`
4. ❌ `text-style`
5. ❌ `foreground-color`
6. ❌ `color-text`

**Settings Auto-Filled:**
- Correct Answer: `1`

**You can then:**
- Edit any answer if needed
- Change the category label
- Save the question
- Or regenerate by clicking the button again

## Visual Indicators

### Answer Field States

#### Normal Answer (Not Selected as Correct)
```
┌─────────────────────────────┐
│ Answer #2              [2]  │
│ ┌─────────────────────────┐ │
│ │ Some incorrect answer   │ │
│ └─────────────────────────┘ │
│ 23 characters               │
└─────────────────────────────┘
```

#### Correct Answer (Highlighted)
```
╔═════════════════════════════╗
║ Answer #1              [1] ✓║  ← Green background
║ ┏━━━━━━━━━━━━━━━━━━━━━━━┓ ║
║ ┃ The correct answer    ┃ ║
║ ┗━━━━━━━━━━━━━━━━━━━━━━━┛ ║
║ 20 characters               ║
╚═════════════════════════════╝
```

### Character Count States

**Normal:**
```
0 characters
```

**Warning (>500 chars):**
```
523 characters  ← Yellow/Orange color
```

**Danger (>800 chars):**
```
847 characters  ← Red color
```

## Button States

| State | Appearance | Enabled | Description |
|-------|------------|---------|-------------|
| **Ready** | 🤖 Generate Answers with AI | ✅ Yes | Ready to generate |
| **Loading** | ⏳ Generating answers... | ❌ No | API call in progress |
| **Success** | 🤖 Generate Answers with AI | ✅ Yes | Generation complete |
| **Error** | 🤖 Generate Answers with AI | ✅ Yes | Can try again |

## Real-World Examples

### Example 1: Programming Question
**Question:** "What is the output of console.log(typeof null) in JavaScript?"

**AI Generated:**
1. ✅ "object"
2. ❌ "null"
3. ❌ "undefined"
4. ❌ "number"
5. ❌ "string"
6. ❌ "boolean"

### Example 2: General Knowledge
**Question:** "What is the capital of France?"

**AI Generated:**
1. ✅ "Paris"
2. ❌ "London"
3. ❌ "Berlin"
4. ❌ "Madrid"
5. ❌ "Rome"
6. ❌ "Brussels"

### Example 3: Technical Question
**Question:** "Which HTTP status code indicates a successful request?"

**AI Generated:**
1. ✅ "200 OK"
2. ❌ "404 Not Found"
3. ❌ "500 Internal Server Error"
4. ❌ "302 Found"
5. ❌ "401 Unauthorized"
6. ❌ "403 Forbidden"

## Where to Find This Feature

**Navigation Path:**
```
Dashboard → Question → Create Question
```

**Or:**
```
Any Quiz → Add Question → Create Question
```

**Note:** The AI generation button ONLY appears when **creating** a new question, not when editing an existing one.

## Tips for Best Results

1. **Be Specific**: "What is a CSS flexbox?" works better than "CSS?"
2. **Use Complete Questions**: Include question marks and proper grammar
3. **Avoid Ambiguity**: Clear questions generate better answers
4. **Technical Terms**: AI handles code, formulas, and technical terms well
5. **Review Before Saving**: Always review AI-generated content

## Troubleshooting Quick Reference

| Issue | Check | Fix |
|-------|-------|-----|
| Button doesn't appear | Creating new question? | Only visible on create, not edit |
| "Error connecting" | LM Studio running? | Start LM Studio server |
| Slow response | Model size? | Use smaller model (7B vs 70B) |
| Poor quality answers | Question clarity? | Rephrase question more clearly |
| Wrong answer marked correct | Auto-set to 1 | Manually change correct answer field |

## Keyboard Shortcuts

- **Tab** - Navigate through answer fields
- **Ctrl+S** - Save question (after reviewing)
- **Esc** - Cancel (if needed)

## Mobile/Tablet Compatibility

The button is fully responsive and works on:
- Desktop computers ✅
- Tablets ✅
- Mobile phones ✅ (button stacks vertically)

---

**Last Updated:** October 2025
**Feature Version:** 1.0


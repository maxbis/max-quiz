# AI Answer Generation Feature

## Overview
This feature allows you to automatically generate 6 answer options for a quiz question using AI through LM Studio's local API.

## Setup Requirements

### 1. Install and Run LM Studio
- Download LM Studio from: https://lmstudio.ai/
- Install and open LM Studio
- Download a suitable language model (recommended: Mistral, Llama 2, or similar)
- Start the local server:
  - In LM Studio, go to the "Local Server" tab
  - Click "Start Server"
  - Ensure it's running on port 1234 (default)

### 2. How to Use

1. Navigate to **Question** > **Create Question**
2. Enter your question in the question field
3. Click the **"🤖 Generate Answers with AI"** button
4. Wait for the AI to generate 6 answers
5. The answers will be automatically filled in:
   - Answer 1 will be the **correct answer**
   - Answers 2-6 will be **incorrect alternatives**
6. The "Correct Answer" field will be automatically set to 1
7. Review and edit the generated answers if needed
8. Save the question

## How It Works

### Backend (QuestionController.php)
- New action: `actionGenerateAnswers()`
- Connects to LM Studio API on `http://localhost:1234`
- Sends a prompt asking for 6 answers (1 correct, 5 incorrect)
- Returns JSON with the generated answers

### Frontend (_form.php)
- "Generate Answers with AI" button (only visible when creating new questions)
- JavaScript function `generateAnswersWithAI()` handles:
  - Validation that a question is entered
  - AJAX call to the backend
  - Auto-filling the answer fields
  - Setting the correct answer to 1
  - Visual feedback (loading spinner)

## API Format

The feature uses the OpenAI-compatible API format that LM Studio provides:

```javascript
POST http://localhost:1234/v1/chat/completions
Content-Type: application/json

{
  "model": "local-model",
  "messages": [
    {
      "role": "system",
      "content": "You are a quiz question assistant..."
    },
    {
      "role": "user",
      "content": "Given the following question, generate exactly 6 answers..."
    }
  ],
  "max_tokens": 500,
  "temperature": 0.7
}
```

### Expected Response Format
```json
{
  "answers": [
    "correct answer",
    "wrong answer 1", 
    "wrong answer 2",
    "wrong answer 3",
    "wrong answer 4",
    "wrong answer 5"
  ]
}
```

## Troubleshooting

### "Error connecting to AI service"
- Make sure LM Studio is running
- Check that the local server is started on port 1234
- Verify you have a model loaded in LM Studio

### "AI did not return 6 answers"
- Try rephrasing your question
- Make sure your question is clear and specific
- Try a different model in LM Studio
- Check that the model has enough context to understand the question

### Slow Response
- This depends on your hardware and the model size
- Smaller models (7B parameters) are faster than larger ones
- Ensure LM Studio is using GPU acceleration if available

## Tips for Best Results

1. **Write clear, specific questions**: The AI generates better answers when the question is unambiguous
2. **Use complete sentences**: Full questions work better than fragments
3. **Review AI output**: Always review and edit the generated answers to ensure quality
4. **Adjust if needed**: You can manually edit any answer after generation
5. **Model selection**: In LM Studio, models like Mistral-7B or Llama-2-7B work well for this task

## Future Enhancements

Possible improvements:
- Allow customization of how many answers to generate
- Let users specify which answer should be correct
- Add support for generating multiple questions at once
- Save AI preferences (temperature, model, etc.)
- Add history of AI-generated content


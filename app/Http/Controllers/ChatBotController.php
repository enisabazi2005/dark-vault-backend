<?php

namespace App\Http\Controllers;

use App\Models\ChatbotKnowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatBotController extends Controller
{
    public function respond(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',  
        ]);
    
        $userInput = trim($validated['message']);
    
        // Debug logging
        \Log::info('Received request with message: ' . $userInput);
    
        // Only check for greeting if it's not a FAQ question
        $faq = $this->getFAQResponse($userInput);
        if ($faq) {
            return response()->json(['reply' => $faq]);
        }
    
        if ($this->isGreeting(strtolower($userInput))) {
            return response()->json(['reply' => 'Hello! How can I assist you today?']);
        }
    
        $openaiResponse = $this->askOpenAI($userInput);
        return response()->json(['reply' => $openaiResponse]);
    }

    private function isGreeting($message)
    {
        $greetings = [
            'hello', 'hi', 'hey', 'good morning', 'good evening', 'greetings', 
            'how are you', "how's it going", 'what’s up', 'howdy'
        ];

        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }
        return false;
    }

    private function getFAQResponse($message)
    {
        $faq = [
            'Who is creator of Dark Vault?' => 'Enis Abazi, born in April 2005, a software developer working on Dark Vault and other projects.',
            'Can I reset password?' => 'Yes, you can go to the login page, reset your password, enter your email, and check your email to reset it.',
            'Can we create groups?' => 'Yes, go to the Dashboard -> Groups section to create a group.',
            'Is everything free?' => 'No, when certain storage is being used (e.g., storing passwords, emails, etc.), you need to pay. For example, the maximum meter knowledge in the dashboard is 100.',
            'Is everything encrypted?' => 'Yes, everything is 100% encrypted and safe.'
        ];
    
        // Debug logging
        \Log::info('Received message: ' . $message);
        
        // Clean the input message
        $cleanMessage = trim($message);
        
        // Direct match first
        if (isset($faq[$cleanMessage])) {
            return $faq[$cleanMessage];
        }
        
        // Case-insensitive match as fallback
        $lowerMessage = strtolower($cleanMessage);
        foreach ($faq as $question => $answer) {
            if (strtolower($question) === $lowerMessage) {
                return $answer;
            }
        }

        foreach ($faq as $question => $answer) {
            if (str_contains(strtolower($question), $lowerMessage)) {
                return $answer;
            }
        }
    
        return null;
    }

    private function askOpenAI($message)
    {
        $apiKey = env('OPENAI_API_KEY');
    
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful, general-purpose assistant. Answer the user’s questions in a friendly and informative manner.'],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);
    
            $responseBody = $response->json();
            \Log::info("OpenAI API Response Body: ", ['response' => $responseBody]);
    
            if (isset($responseBody['choices'][0]['message']['content'])) {
                return $responseBody['choices'][0]['message']['content'];
            } else {
                return "Sorry, I couldn't figure that out.";
            }
    
        } catch (\Exception $e) {
            \Log::error("Error with OpenAI API Request: " . $e->getMessage());
            return "Sorry, there was an issue with processing your request.";
        }
    }
}

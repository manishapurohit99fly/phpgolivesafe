<?php

namespace App\Traits;

use App\Mail\DynamicMail;
use App\Models\EmailTemplates;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

trait Common_trait
{
    public function create_unique_slug($string = '', $table = '', $field = 'slug', $col_name = null, $old_slug = null)
    {
        $slug = Str::of($string)->slug('-');
        $slug = strtolower($slug);

        $i = 0;
        $params = array();
        $params[$field] = $slug;
        if ($col_name) {
            $params["$col_name"] = "<> $old_slug";
        }

        while (DB::table($table)->where($params)->count()) {
            if (!preg_match('/-{1}[0-9]+$/', $slug)) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace('/[0-9]+$/', ++$i, $slug);
            }
            $params[$field] = $slug;
        }
        return $slug;
    }

    public function file_upload($file, $path)
    {
        $path = Storage::disk('local')->put($path, $file);
        return $path;
    }

    // public function file_upload($file, $path, $fileName = null)
    // {
    //     $disk = config('constants.file_upload_location');

    //     // $path = Storage::disk(config('constants.file_upload_location'))->put($path, $file);

    //     if (!$fileName) {
    //         $fileName = time() . '-' . $file->getClientOriginalName();
    //     }

    //     $path = $file->storeAs($path, $fileName, $disk);
    //     return $path;
    // }

    public function deleteFile($filePath)
    {
        if ($filePath) {
            $disk = FILE_UPLOAD_LOCATION;

            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
                return true;
            }
        }

        return false;
    }

    public function sendOTP($to = '', $data = [], $message = '')
    {
        $msg = $this->replacePlaceholders($data, $message);

        //return true;

        $postData = [
            'To' => $to,
            'From' => '+' . env('TWILIO_FROM_NUMBER'),
            'Body' => $msg,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/' . env('TWILIO_ACCOUNT_SID') . '/Messages.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode(env('TWILIO_ACCOUNT_SID') . ':' . env('TWILIO_AUTH_TOKEN'))
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            Log::error("Twilio OTP sending failed", [
                'to' => $to,
                'error' => $error
            ]);
            return false;
        } else {
            Log::info("Twilio OTP sent successfully", [
                'to' => $to,
                'response' => $response
            ]);
            return true;
        }
    }

    public function sendEmail($email, Mailable $mailable, $queue = true)
    {
        try {
            if ($queue) {
                Mail::to($email)->queue($mailable);
                Log::info("Email queued successfully to: " . $email);
            } else {
                Mail::to($email)->send($mailable);
                Log::info("Email sent successfully to: " . $email);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email to: {$email}. Error: " . $e->getMessage());
            return false;
        }
    }

    function replacePlaceholders($replacements,  $message): string
    {
        $hasPlaceholder = false;

        foreach ($replacements as $key => $value) {
            if (strpos($message, "##$key##") !== false) {
                $hasPlaceholder = true;
                $message = str_replace("##$key##", $value, $message);
            }
        }
        return $hasPlaceholder ? $message : $message;
    }

 
    public function sendDynamicEmail(string $email, string $templateName, array $data = [], bool $queue = true): bool
    {
        $template = EmailTemplates::findByName($templateName);

        if (!$template) {
            Log::error("Email template not found or inactive.", ['template' => $templateName]);
            return false;
        }

        $subject = $this->parseTemplate($template->subject, $data);
        $body    = $this->parseTemplate($template->body, $data);

        return $this->sendEmail($email, new DynamicMail($subject, $body), $queue);
    }

  
    private function parseTemplate(string $body, array $data): string
    {
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return $body;
    }

    public function sendPushNotification($deviceToken, string $title, string $body, array $data = []): bool
    {
        try {
            $serverKey = env('FCM_SERVER_KEY');

            if (empty($serverKey)) {
                Log::error('FCM_SERVER_KEY is not set in .env');
                return false;
            }

            if (empty($deviceToken)) {
                Log::warning('Push notification skipped: device token is empty');
                return false;
            }

            $payload = [
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
            ];

            if (!empty($data)) {
                $payload['data'] = $data;
            }

            // Support single token or multiple tokens
            if (is_array($deviceToken)) {
                $payload['registration_ids'] = array_values($deviceToken);
            } else {
                $payload['to'] = $deviceToken;
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://fcm.googleapis.com/fcm/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: key=' . $serverKey,
                ],
            ]);

            $response  = curl_exec($curl);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($curlError) {
                Log::error('Push notification cURL error', ['error' => $curlError]);
                return false;
            }

            $result = json_decode($response, true);

            if (isset($result['failure']) && $result['failure'] > 0) {
                Log::warning('Push notification sent with failures', [
                    'token'    => is_array($deviceToken) ? implode(',', $deviceToken) : $deviceToken,
                    'title'    => $title,
                    'response' => $result,
                ]);
                return false;
            }

            Log::info('Push notification sent successfully', [
                'token' => is_array($deviceToken) ? implode(',', $deviceToken) : $deviceToken,
                'title' => $title,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Push notification exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}

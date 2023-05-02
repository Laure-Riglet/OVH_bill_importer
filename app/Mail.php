<?php

class Mail
{
    // Sending email with attachment
    public static function sendMail(
        string $fileAttachment,
        string $fileName,
        string $mailMessage,
        string $subject,
        string $toAddresses,
        string $ccAddresses,
        string $bccAddresses,
        string $fromMail
    ): string {

        $fileAttachment = trim($fileAttachment);
        $from           = $fromMail;
        $pathInfo       = pathinfo($fileAttachment);
        $attachmentName  = $fileName . (
            (isset($pathInfo['extension'])) ? "." . $pathInfo['extension'] : ""
        );
        $attachment    = chunk_split(base64_encode(file_get_contents($fileAttachment)));
        $boundary      = "PHP-mixed-" . md5(time());
        $boundWithPre  = "\n--" . $boundary;
        $headers   = "From: $from";
        $headers  .= "\nReply-To: $from";
        $headers  .= "\nContent-Type: multipart/mixed; boundary=\"" . $boundary . "\"";
        if ($ccAddresses != "") {
            $headers .= "\nCc: " . $ccAddresses;
        }
        if ($bccAddresses != "") {
            $headers .= "\nBcc: " . $bccAddresses;
        }
        $message   = $boundWithPre;
        $message  .= "\nContent-Type: text/plain; charset=UTF-8\n";
        $message  .= "\n$mailMessage";
        $message .= $boundWithPre;
        $message .= "\nContent-Type: application/octet-stream; name=\"" . $attachmentName . "\"";
        $message .= "\nContent-Transfer-Encoding: base64\n";
        $message .= "\nContent-Disposition: attachment\n";
        $message .= $attachment;
        $message .= $boundWithPre . "--";

        try {
            mail($toAddresses, $subject, $message, $headers);
        } catch (\Exception $e) {
            return 'Email sending failed: ' . $e->getMessage() . PHP_EOL;
        }

        return 'Email sent' . PHP_EOL;
    }
}

<?php

namespace App\Services;

class EmailService
{
    public function send_email($parameter = array(), $success_message = '', $error_message = '')
    {
        if (function_exists('curl_version')) {
            $parameter['comp_name'] = "WoundMed Inc.";
            $parameter['mail_type'] = 1;
            $parameter['dev_mode']  = 0;
            $parameter['debug']     = 0;


            if ((!empty($parameter['attachment']) && ($parameter['mail_type'] == 1)) || (!empty($parameter['comb']))) {
                $parameter['attachments'] = implode(',', $this->file_uploader($parameter['attachment'], $parameter['comb']));
            }

            $parameter['to'] = (is_array($parameter['to'])) ? implode(',', $parameter['to']) : $parameter['to'];
            if (!empty($parameter['cc'])) {
                $parameter['cc'] = is_array($parameter['cc']) ? implode(',', $parameter['cc']) : $parameter['cc'];
            }

            $ch              = curl_init();
            $mode            = ($parameter['debug'] == true) ? 'test_send_email' : 'send_email';

            $url = "http://proweaveremail.com/email/" . $mode;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);

            $response = json_decode(curl_exec($ch), true);
            $info     = curl_getinfo($ch);

            if (!empty($parameter['debug']) && $parameter['debug'] == true) {
                echo "<pre>";
                print_r($response);
                exit;
            }

            if ($response['response'] == 'sent') {
                if (!empty($success_message)) {
                    return $success_message;
                } else {
                    return 'THANK YOU for sending us a message! We will be in touch with you soon.';
                }

                if (!empty($parameter['confirmation_email'])) {
                    $this->confirmation_email($parameter['confirmation_email'], $parameter['from']);
                }
            } else {
                if (!empty($error_message)) {
                    return $error_message;
                } else {
                    return 'Failed to send email. Please try again.';
                }
            }

            curl_close($ch);
        } else {
            return 'Failed to send email. Please enable Curl.';
        }
    }

    public function confirmation_email($message, $from = '')
    {
        $confirmation_parameter = array(
            'email_address' => $_POST['Email_Address'] ?? '', // You may want to pass this as a parameter
            'from' => $from,
            'message' => $message,
        );

        $url = "http://proweaveremail.com/email/confirmation_email";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $confirmation_parameter);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
    }


    function file_uploader($files, $comb = 0)
    {
        for ($i = 0; $i < count($files); $i++) {
            $ch = curl_init();

            $file      = ($comb == 0) ? $files['attachment']['tmp_name'][$i] : $files[$i];
            $mime      = ($comb == 0) ? $this->get_mime_type($files['attachment']['type'][$i]) : $this->get_mime_type($files[$i]);
            $file_name = ($comb == 0) ? basename($files['attachment']['name'][$i]) : basename($files[$i]);

            $array_ = (class_exists('CURLFile')) ? new \CURLFile($file, $mime, $file_name) : "@$file;filename=$file_name;type=$mime";

            if ($comb == 0) {
                $post_fields = array('my_file' => $array_,);
            } else {
                $post_fields = array('my_file' => $array_,);
            }

            $url = "http://proweaveremail.com/email/file_uploader";
            // Set the url
            curl_setopt($ch, CURLOPT_URL, $url);

            // Set the result output to be a string.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

            // curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);

            $response = json_decode(curl_exec($ch), true);
            $file_attachment[] = $response;
            curl_close($ch);
        }
        return $file_attachment;
    }

    function get_mime_type($filename)
    {
        $idx = explode('.', $filename);
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode - 1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset($mimet[$idx])) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }
}

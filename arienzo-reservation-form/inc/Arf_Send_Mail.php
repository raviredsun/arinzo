<?php


class Arf_Send_Mail
{
    protected $data;
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Message
     * @param $message
     * @return string
     */
    protected function message($message) {
        return $message;
    }

    /**
     * Mail Headers
     * @param $email
     * @return string
     */
    protected function headers($email) {
        return 'From: '. $email . "\r\n" .
            'Reply-To: ' . $email . "\r\n";
    }

    protected function subject($subject) {
        return $subject;
    }

    /**
     * Send Function
     * @param $to
     * @param $subject
     * @param $message
     * @param $headers
     * @return bool
     */
    protected function __send($to, $subject, $message, $headers) {
        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * @return bool
     */
    public function send() {
        if(empty($this->data))
            return false;
        $from = $this->data['from'];
        $to = $this->data['to'];
        $message = $this->data['message'];
        $subject = $this->data['subject'];
        $m_headers = $this->headers($from);
        $m_subject = $this->subject($subject);
        $m_message = $this->message($message);
        return $this->__send($to, $m_subject, $m_message, $m_headers);
    }
}
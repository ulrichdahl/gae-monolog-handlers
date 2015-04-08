<?php
namespace UlrichDahl\Monolog\Handler;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\MailHandler;
use Monolog\Logger;

class GoogleAppEngineMailHandler extends MailHandler
{
	private $to;
	private $subject;
	private $from;

	/**
	 * @param string    $to
	 * @param string    $subject {{Level}} will be replaced with the log level of the message
	 * @param string    $from
	 * @param integer   $level   The minimum logging level at which this handler will be triggered
	 * @param Boolean   $bubble  Whether the messages that are handled can bubble up the stack or not
	 */
	public function __construct($to, $subject, $from, $level = Logger::ERROR, $bubble = true)
	{
		$this->to = $to;
		$this->subject = $subject;
		$this->from = $from;
		parent::__construct($level, $bubble);
		$this->setFormatter(new HtmlFormatter());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function send($content, array $records)
	{
		$message = $this->buildMessage($content, $records);
		$message->send();
	}

	/**
	 * Creates instance of Swift_Message to be sent
	 *
	 * @param string $content formatted email body to be sent
	 * @param array  $records Log records that formed the content
	 * @return \google\appengine\api\mail\Message
	 */
	protected function buildMessage($content, array $records)
	{
		$message = new \google\appengine\api\mail\Message();

		$message->setHtmlBody($content);
		$message->setTextBody(var_export($records, true));
		$message->addTo($this->to);
		$message->setSender($this->from);
		$message->setSubject(str_replace("{{Level}}", $records[0]['level_name'], $this->subject));

		return $message;
	}
}

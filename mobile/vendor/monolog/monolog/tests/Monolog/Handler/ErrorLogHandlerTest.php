<?php
//dezend by  QQ:2172298892
namespace Monolog\Handler;

function error_log()
{
	$GLOBALS['error_log'][] = func_get_args();
}

class ErrorLogHandlerTest extends \Monolog\TestCase
{
	protected function setUp()
	{
		$GLOBALS['error_log'] = array();
	}

	public function testShouldNotAcceptAnInvalidTypeOnContructor()
	{
		new ErrorLogHandler(42);
	}

	public function testShouldLogMessagesUsingErrorLogFuncion()
	{
		$type = ErrorLogHandler::OPERATING_SYSTEM;
		$handler = new ErrorLogHandler($type);
		$handler->setFormatter(new \Monolog\Formatter\LineFormatter('%channel%.%level_name%: %message% %context% %extra%', null, true));
		$handler->handle($this->getRecord(\Monolog\Logger::ERROR, "Foo\nBar\r\n\r\nBaz"));
		$this->assertSame("test.ERROR: Foo\nBar\r\n\r\nBaz [] []", $GLOBALS['error_log'][0][0]);
		$this->assertSame($GLOBALS['error_log'][0][1], $type);
		$handler = new ErrorLogHandler($type, \Monolog\Logger::DEBUG, true, true);
		$handler->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, true));
		$handler->handle($this->getRecord(\Monolog\Logger::ERROR, "Foo\nBar\r\n\r\nBaz"));
		$this->assertStringMatchesFormat('[%s] test.ERROR: Foo', $GLOBALS['error_log'][1][0]);
		$this->assertSame($GLOBALS['error_log'][1][1], $type);
		$this->assertStringMatchesFormat('Bar', $GLOBALS['error_log'][2][0]);
		$this->assertSame($GLOBALS['error_log'][2][1], $type);
		$this->assertStringMatchesFormat('Baz [] []', $GLOBALS['error_log'][3][0]);
		$this->assertSame($GLOBALS['error_log'][3][1], $type);
	}
}

?>
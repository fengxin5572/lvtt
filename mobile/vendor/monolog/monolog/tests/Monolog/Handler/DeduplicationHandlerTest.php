<?php
//dezend by  QQ:2172298892
namespace Monolog\Handler;

class DeduplicationHandlerTest extends \Monolog\TestCase
{
	public function testFlushPassthruIfAllRecordsUnderTrigger()
	{
		$test = new TestHandler();
		@unlink(sys_get_temp_dir() . '/monolog_dedup.log');
		$handler = new DeduplicationHandler($test, sys_get_temp_dir() . '/monolog_dedup.log', 0);
		$handler->handle($this->getRecord(\Monolog\Logger::DEBUG));
		$handler->handle($this->getRecord(\Monolog\Logger::INFO));
		$handler->flush();
		$this->assertTrue($test->hasInfoRecords());
		$this->assertTrue($test->hasDebugRecords());
		$this->assertFalse($test->hasWarningRecords());
	}

	public function testFlushPassthruIfEmptyLog()
	{
		$test = new TestHandler();
		@unlink(sys_get_temp_dir() . '/monolog_dedup.log');
		$handler = new DeduplicationHandler($test, sys_get_temp_dir() . '/monolog_dedup.log', 0);
		$handler->handle($this->getRecord(\Monolog\Logger::ERROR, 'Foo:bar'));
		$handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, "Foo\nbar"));
		$handler->flush();
		$this->assertTrue($test->hasErrorRecords());
		$this->assertTrue($test->hasCriticalRecords());
		$this->assertFalse($test->hasWarningRecords());
	}

	public function testFlushSkipsIfLogExists()
	{
		$test = new TestHandler();
		$handler = new DeduplicationHandler($test, sys_get_temp_dir() . '/monolog_dedup.log', 0);
		$handler->handle($this->getRecord(\Monolog\Logger::ERROR, 'Foo:bar'));
		$handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, "Foo\nbar"));
		$handler->flush();
		$this->assertFalse($test->hasErrorRecords());
		$this->assertFalse($test->hasCriticalRecords());
		$this->assertFalse($test->hasWarningRecords());
	}

	public function testFlushPassthruIfLogTooOld()
	{
		$test = new TestHandler();
		$handler = new DeduplicationHandler($test, sys_get_temp_dir() . '/monolog_dedup.log', 0);
		$record = $this->getRecord(\Monolog\Logger::ERROR);
		$record['datetime']->modify('+62seconds');
		$handler->handle($record);
		$record = $this->getRecord(\Monolog\Logger::CRITICAL);
		$record['datetime']->modify('+62seconds');
		$handler->handle($record);
		$handler->flush();
		$this->assertTrue($test->hasErrorRecords());
		$this->assertTrue($test->hasCriticalRecords());
		$this->assertFalse($test->hasWarningRecords());
	}

	public function testGcOldLogs()
	{
		$test = new TestHandler();
		@unlink(sys_get_temp_dir() . '/monolog_dedup.log');
		$handler = new DeduplicationHandler($test, sys_get_temp_dir() . '/monolog_dedup.log', 0);
		$record = $this->getRecord(\Monolog\Logger::ERROR);
		$record['datetime']->modify('-1day -10seconds');
		$handler->handle($record);
		$record2 = $this->getRecord(\Monolog\Logger::CRITICAL);
		$record2['datetime']->modify('-1day -10seconds');
		$handler->handle($record2);
		$record3 = $this->getRecord(\Monolog\Logger::CRITICAL);
		$record3['datetime']->modify('-30seconds');
		$handler->handle($record3);
		$handler->flush();
		$this->assertSame($record['datetime']->getTimestamp() . ":ERROR:test\n" . $record2['datetime']->getTimestamp() . ":CRITICAL:test\n" . $record3['datetime']->getTimestamp() . ":CRITICAL:test\n", file_get_contents(sys_get_temp_dir() . '/monolog_dedup.log'));
		$this->assertTrue($test->hasErrorRecords());
		$this->assertTrue($test->hasCriticalRecords());
		$this->assertFalse($test->hasWarningRecords());
		$test->clear();
		$this->assertFalse($test->hasErrorRecords());
		$this->assertFalse($test->hasCriticalRecords());
		$handler->handle($record = $this->getRecord(\Monolog\Logger::ERROR));
		$handler->handle($record2 = $this->getRecord(\Monolog\Logger::CRITICAL));
		$handler->flush();
		$this->assertSame($record3['datetime']->getTimestamp() . ":CRITICAL:test\n" . $record['datetime']->getTimestamp() . ":ERROR:test\n" . $record2['datetime']->getTimestamp() . ":CRITICAL:test\n", file_get_contents(sys_get_temp_dir() . '/monolog_dedup.log'));
		$this->assertTrue($test->hasErrorRecords());
		$this->assertTrue($test->hasCriticalRecords());
		$this->assertFalse($test->hasWarningRecords());
	}

	static public function tearDownAfterClass()
	{
		@unlink(sys_get_temp_dir() . '/monolog_dedup.log');
	}
}

?>

<?php
//dezend by  QQ:2172298892
namespace Monolog\Formatter;

class LogglyFormatterTest extends \Monolog\TestCase
{
	public function testConstruct()
	{
		$formatter = new LogglyFormatter();
		$this->assertEquals(LogglyFormatter::BATCH_MODE_NEWLINES, $formatter->getBatchMode());
		$formatter = new LogglyFormatter(LogglyFormatter::BATCH_MODE_JSON);
		$this->assertEquals(LogglyFormatter::BATCH_MODE_JSON, $formatter->getBatchMode());
	}

	public function testFormat()
	{
		$formatter = new LogglyFormatter();
		$record = $this->getRecord();
		$formatted_decoded = json_decode($formatter->format($record), true);
		$this->assertArrayHasKey('timestamp', $formatted_decoded);
		$this->assertEquals(new \DateTime($formatted_decoded['timestamp']), $record['datetime']);
	}
}

?>

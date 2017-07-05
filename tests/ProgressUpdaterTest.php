<?php

/**
 * @coversDefaultClass \Manticorp\ProgressUpdater
 */
class ProgressUpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testObjectCreated()
    {
        // Arrange
        $pu = new \Manticorp\ProgressUpdater();

        // Assert
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        return $pu;
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::__construct
     */
    public function testOptionsCreatedOnConstruct()
    {
        // Arrange
        $options = array(
            'lineBreak'    => "<br />",
            'filename'     => __DIR__.DIRECTORY_SEPARATOR.'testFilenameSetting2.txt',
            'totalStages'  => 1,
            'autocalc'     => True, // Will autocalculate other status values if possible (e.g. if totalItems and Complete items are set, pcComplete will be autogenerated)
            'handleErrors' => True
        );
        $pu = new \Manticorp\ProgressUpdater($options);

        // Assert
        $this->assertEquals($options,$pu->getOptions());
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::setOption
     * @covers ::setOpt
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessageRegExp /\w+? has no option \w+?/
     */
    public function testSettingUnavailableOption($pu)
    {
        $pu->setOption('foo','bar');
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::getOption
     * @covers ::getOpt
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessageRegExp /\w+? has no option \w+?/
     */
    public function testGettingUnavailableOption($pu)
    {
        $pu->getOption('foo');
    }

    /**
     * @test
     * @depends testObjectCreated
     * @expectedException BadMethodCallException
     * @expectedExceptionMessageRegExp /Method: \w+? does not exists in \w+?/
     */
    public function testUnavailableMethodUsage($pu)
    {
        $pu->fooBar('foo','bar');
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::setOption
     * @covers ::setOpt
     */
    public function testSettingOption($pu)
    {
        $pu->setOption('totalStages',1);

        $this->assertEquals($pu->getOption('totalStages'), 1);

        $pu->setOpt('totalStages',2);

        $this->assertEquals($pu->getOption('totalStages'), 2);
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::nextStage
     */
    public function testNextStageStatus($pu)
    {
        $stageOptions = array(
            'name'          => 'Foo',
            'message'       => 'Bar',
            'totalItems'    => 100,
            'rate'          => 1,
        );

        $pu->nextStage($stageOptions);
        $result = $pu->getStage();

        $stage = array(
            'name'          => null,
            'message'       => null,
            'stageNum'      => 1,
            'totalItems'    => 1,
            'completeItems' => 0,
            'pcComplete'    => 0.0,
            'rate'          => null,
            'timeRemaining' => null,
            'startTime'     => null,
            'curTime'       => null,
            'exceptions'    => array(),
            'warnings'      => array(),
        );

        // should be
        $stageOptions = array_merge($stage,$stageOptions);

        $result->startTime = $stageOptions['startTime'] = null;
        $result->curTime   = $stageOptions['curTime']   = null;

        $this->assertEquals($stageOptions, $result->toArray());
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::__call
     */
    public function testMagicSetters($pu)
    {
        $pu = new \Manticorp\ProgressUpdater();
        $pu->setStatusMessage('Hello');
        $statusMessage = $pu->getStatus()['message'];
        $this->assertEquals('Hello',$statusMessage);

        $pu->setStageMessage('Hello');
        $stageMessage = $pu->getStage()->message;
        $this->assertEquals('Hello',$stageMessage);
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::incrementStageItems
     */
    public function testAutoCalcSetting()
    {
        // Arrange
        $options = array(
            'lineBreak'   => "\n",
            'filename'    => __DIR__.DIRECTORY_SEPARATOR.'testFilenameSetting3.txt',
            'totalStages' => 1,
            'autocalc'    => True,
        );
        $pu = new \Manticorp\ProgressUpdater($options);

        $stageOptions = array(
            'name'          => 'Foo',
            'message'       => 'Bar',
            'totalItems'    => 100,
            'rate'          => 1,
        );

        $pu->nextStage($stageOptions);

        sleep(0.5);
        $pu->incrementStageItems();
        sleep(0.5);
        $pu->incrementStageItems();
        $stage = $pu->getStage();

        $this->assertGreaterThanOrEqual(2,    $stage->rate);
        $this->assertGreaterThanOrEqual(0.02, $stage->pcComplete);
        unlink($options['filename']);

    }

    /**
     * @test
     * @depends testObjectCreated
     * @depends testMagicSetters
     * @covers ::__call
     */
    public function testEveryNonGetterMethodReturnsInstance($pu)
    {
        $pu = new \Manticorp\ProgressUpdater();

        $pu = $pu->setStatusMessage('Hello');
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->setStageMessage('Hello');
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->publishStatus();
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->generateFilename();
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->setOptions(array());
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->setOpts(array());
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->nextStage();
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->updateStage(array());
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->incrementStageItems();
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->setOption('lineBreak','foo');
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->setOpt('lineBreak','foo');
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $pu = $pu->totallyComplete();
        $this->assertInstanceOf('\Manticorp\ProgressUpdater', $pu);

        $this->expectOutputString('{"message":"Process Complete","totalStages":1,"remaining":0,"error":false,"complete":true}');
    }

    /**
     * @test
     * @depends testObjectCreated
     * @depends testMagicSetters
     * @covers ::__call
     */
    public function testMagicGetters($pu)
    {
        $pu->setStatusMessage('Hello');
        $statusMessage = $pu->getStatusMessage();
        $this->assertEquals('Hello',$statusMessage);

        $pu->setStageMessage('Hello');
        $stageMessage = $pu->getStageMessage();
        $this->assertEquals('Hello',$stageMessage);
    }

    /**
     * @test
     * @depends testObjectCreated
     * @depends testMagicSetters
     * @covers ::__call
     */
    public function testCreatesProgressFile()
    {
        // Arrange
        $options = array(
            'lineBreak'   => "\n",
            'filename'    => __DIR__.DIRECTORY_SEPARATOR.'testFilenameSetting1.txt',
            'totalStages' => 1,
            'autocalc'    => True,
        );
        $pu = new \Manticorp\ProgressUpdater($options);

        $pu->nextStage();
        $status = json_encode($pu->getStatusArray());

        $this->assertFileExists($options['filename']);
        $this->assertJsonStringEqualsJsonFile($options['filename'], $status);
        unlink($options['filename']);
    }

    /**
     * @test
     * @depends testObjectCreated
     * @covers ::totallyComplete
     */
    public function testTotallyCompleteStatus($pu)
    {
        $pu->totallyComplete();

        $status = array(
            'message'       => 'Process Complete',
            'totalStages'   => 2,
            'remaining'     => 0,
            'error'         => false,
            'complete'      => true
        );

        $this->assertEquals($pu->getStatus(), $status);

        $this->expectOutputString(json_encode($status));
    }
}
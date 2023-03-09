<?php

declare(strict_types=1);

namespace OpenAPI\Processor;

use Membrane\Filter\String\JsonDecode;
use Membrane\OpenAPI\Processor\Json;
use Membrane\Processor;
use Membrane\Processor\Field;
use Membrane\Result\FieldName;
use Membrane\Result\Message;
use Membrane\Result\MessageSet;
use Membrane\Result\Result;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Json::class)]
#[UsesClass(JsonDecode::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldName::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageSet::class)]
#[UsesClass(Result::class)]
class JsonTest extends TestCase
{
    #[Test]
    public function toStringTest(): void
    {
        $expected = "\"pet\":\n\t- convert from json to a PHP value.\n\t- condition";
        $wrapped = self::createMock(Field::class);
        $sut = new Json($wrapped);

        $wrapped->expects($this->once())
            ->method('processes')
            ->willReturn('pet');

        $wrapped->expects(($this->once()))
            ->method('__toString')
            ->willReturn("\"pet\":\n\t- condition");

        $actual = (string)$sut;

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function toPHPTest(): void
    {
        $sut = new Json(new Field('a'));

        $actual = $sut->__toPHP();

        self::assertEquals($sut, eval('return ' . $actual . ';'));
    }

    #[Test]
    public function processesTest(): void
    {
        $expected = 'fieldName that observer processes';
        $observer = self::createMock(Processor::class);
        $observer->expects($this->once())
            ->method('processes')
            ->willReturn($expected);
        $sut = new Json($observer);

        $actual = $sut->processes();

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function processStopsEarlyIfJsonDecodeFails(): void
    {
        $expected = Result::invalid(
            5,
            new MessageSet(
                new FieldName('', ''),
                new Message('JsonDecode Filter expects a string value, %s passed instead', ['integer'])
            )
        );
        $observer = self::createMock(Processor::class);
        $observer->expects($this->never())
            ->method('process');
        $sut = new Json($observer);

        $actual = $sut->process(new FieldName(''), 5);

        self::assertEquals($expected, $actual);
    }

    #[Test]
    public function processTest(): void
    {
        $expected = Result::valid(['id' => 5]);
        $observer = self::createMock(Processor::class);
        $observer->expects($this->once())
            ->method('process')
            ->willReturn($expected);
        $sut = new Json($observer);

        $actual = $sut->process(new FieldName(''), '{"id" : 5}');

        self::assertEquals($expected, $actual);
    }
}

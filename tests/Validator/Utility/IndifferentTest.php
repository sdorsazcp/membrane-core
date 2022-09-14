<?php

namespace Validator\Utility;

use Membrane\Result\Result;
use Membrane\Validator\Utility\Indifferent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Membrane\Validator\Utility\Indifferent
 * @uses   \Membrane\Result\Result
 */
class IndifferentTest extends TestCase
{
    public function dataSets(): array
    {
        return [
            [1], [1.1], ['one'], [false], [null],
        ];
    }

    /**
     * @test
     * @dataProvider dataSets
     */
    public function IndifferentAlwaysReturnsNoResult($input): void
    {
        $indifferent = new Indifferent();
        $expected = Result::noResult($input);

        $result = $indifferent->validate($input);

        self::assertEquals($expected, $result);
    }
}

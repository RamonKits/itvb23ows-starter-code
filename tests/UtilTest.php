<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
include_once 'util.php';

class UtilTest extends TestCase
{
    public function testGetAvailablePieces()
    {
        $hand = [[
            'Q' => 1,
            'B' => 2,
            'S' => 2,
            'A' => 3,
            'G' => 3,
        ],[
            'Q' => 1,
            'B' => 1,
            'S' => 1,
            'A' => 3,
            'G' => 3,
        ],[
            'Q' => 1,
            'B' => 1,
            'S' => 1,
            'A' => 3,
            'G' => 0,
        ],[
            'Q' => 1,
            'B' => 1,
            'S' => 1,
            'A' => 0,
            'G' => 0,
        ],[
            'Q' => 1,
            'B' => 0,
            'S' => 0,
            'A' => 0,
            'G' => 0,
        ],[
            'Q' => 0,
            'B' => 0,
            'S' => 0,
            'A' => 0,
            'G' => 0,
        ]];

        $this->assertEquals(getAvailablePieces($hand[0]), ['Q', 'B', 'S', 'A', 'G']);
        $this->assertEquals(getAvailablePieces($hand[1]), ['Q', 'B', 'S', 'A', 'G']);
        $this->assertEquals(getAvailablePieces($hand[2]), ['Q', 'B', 'S', 'A']);
        $this->assertEquals(getAvailablePieces($hand[3]), ['Q', 'B', 'S']);
        $this->assertEquals(getAvailablePieces($hand[4]), ['Q']);
        $this->assertEquals(getAvailablePieces($hand[5]), []);
    }

    public function testGetValidPlacements()
    {
        $board = [
            '0,0' => [['0', 'Q']],
        ];
        $hand = [[
            'Q' => 0,
            'B' => 2,
            'S' => 2,
            'A' => 3,
            'G' => 3,
        ]];
        $this->assertEquals(getValidPlacements('0', $hand[0], $board), ['0,1', '0,-1', '1,0', '-1,0', '-1,1', '1,-1']);

        $board = [
            '0,0' => [['1', 'Q']],
        ];
        $hand = [[
            'Q' => 1,
            'B' => 2,
            'S' => 2,
            'A' => 3,
            'G' => 3,
        ]];
        $this->assertEquals(getValidPlacements('0', $hand[0], $board), ['0,1', '0,-1', '1,0', '-1,0', '-1,1', '1,-1']);

        $board = [
            '0,0' => [['0', 'Q']],
            '0,1' => [['1', 'Q']],
        ];
        $hand = [[
            'Q' => 0,
            'B' => 2,
            'S' => 2,
            'A' => 3,
            'G' => 3,
        ]];
        $this->assertEquals(getValidPlacements('0', $hand[0], $board), ['0,-1', '-1,0', '1,-1']);
    }

    public function testGetOwnTiles()
    {
        $board = [
            '0,0' => [['0', 'Q']],
            '0,1' => [['1', 'Q']],
        ];
        $this->assertEquals(getOwnTiles('0', $board), ['0,0']);
        $this->assertEquals(getOwnTiles('1', $board), ['0,1']);

        $board = [
            '0,0' => [['0', 'Q']],
            '0,1' => [['1', 'Q']],
            '1,0' => [['0', 'Q']],
            '1,1' => [['1', 'Q']],
        ];
        $this->assertEquals(getOwnTiles('0', $board), ['0,0', '1,0']);
        $this->assertEquals(getOwnTiles('1', $board), ['0,1', '1,1']);
    }
}

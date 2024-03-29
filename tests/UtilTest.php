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

    public function testSlide()
    {
        $board = [
            '0,0' => [['0', 'Q']],
            '1,0' => [['1', 'Q']],
        ];
        // Queen should be able to slide to (0, 1)
        $this->assertTrue(slide($board, '0,0', '0,1'));
    }

    public function testMoveGrasshopper()
    {
        // moves by jumping in a straight line to a field immediately behind another tile in the direction of the jump
        $board = [
            '0,0' => [['0', 'G']],
            '0,1' => [['1', 'Q']],
        ];
        $this->assertTrue(moveGrasshopper($board, '0,0', '0,2'));
        $this->assertFalse(moveGrasshopper($board, '0,0', '0,3'));
        $this->assertFalse(moveGrasshopper($board, '0,0', '1,1'));

        $board = [
            '0,0' => [['0', 'G']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'Q']],
        ];
        $this->assertTrue(moveGrasshopper($board, '0,0', '0,3'));
        $this->assertFalse(moveGrasshopper($board, '0,0', '0,4'));
        $this->assertFalse(moveGrasshopper($board, '0,0', '1,2'));

        // may not move to the field where it is already standing
        $board = [
            '0,0' => [['0', 'G']],
            '0,1' => [['1', 'Q']],
        ];
        $this->assertFalse(moveGrasshopper($board, '0,0', '0,0'));

        // must jump over at least one tile
        $board = [
            '0,0' => [['0', 'G']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'Q']],
        ];
        $this->assertFalse(moveGrasshopper($board, '0,0', '1,0'));

        // may not jump to an occupied field
        $board = [
            '0,0' => [['0', 'G']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'Q']],
        ];
        $this->assertFalse(moveGrasshopper($board, '0,0', '0,2'));

        // may not jump over empty fields
        $board = [
            '0,0' => [['0', 'G']],
            '0,1' => [['1', 'Q']],
            '1,1' => [['0', 'Q']],
        ];
        $this->assertFalse(moveGrasshopper($board, '0,0', '1,2'));
    }

    public function testMoveAnt()
    {
        // moves by shifting an unlimited number of times
        $board = [
            '0,0' => [['0', 'A']],
            '0,1' => [['1', 'Q']],
        ];
        $this->assertTrue(moveAnt($board, '0,0', '0,2'));
        $this->assertFalse(moveAnt($board, '0,0', '0,3'));
        $this->assertTrue(moveAnt($board, '0,0', '1,1'));

        // may not move to the field where he is already standing
        $board = [
            '0,0' => [['0', 'A']],
            '0,1' => [['1', 'Q']],
        ];
        $this->assertFalse(moveAnt($board, '0,0', '0,0'));

        // may only be moved over and into empty fields
        $board = [
            '0,0' => [['0', 'A']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'Q']],
            '1,1' => [['1', 'G']],
        ];
        $this->assertFalse(moveAnt($board, '0,0', '0,2'));
        $this->assertFalse(moveAnt($board, '0,0', '1,1'));
    }
}

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

    public function testMoveSpider()
    {
        // moves by shifting exactly three times
        $board = [
            '0,0' => [['0', 'S']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'B']],
            '0,3' => [['1', 'B']],
            '0,4' => [['0', 'B']],
        ];
        $this->assertFalse(moveSpider($board, '0,0', '1,3'));
        $this->assertTrue(moveSpider($board, '0,0', '1,2'));
        $this->assertFalse(moveSpider($board, '0,0', '-1,4'));
        $this->assertTrue(moveSpider($board, '0,0', '-1,3'));

        // may not move to the field where he is already standing
        $board = [
            '0,0' => [['0', 'S']],
            '0,1' => [['1', 'Q']],
        ];
        $this->assertFalse(moveSpider($board, '0,0', '0,0'));

        // may only be moved over and into empty fields
        $board = [
            '0,0' => [['0', 'S']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'Q']],
            '1,1' => [['1', 'G']],
        ];
        $this->assertFalse(moveSpider($board, '0,0', '0,2'));
        $this->assertFalse(moveSpider($board, '0,0', '1,1'));
    }

    public function testMayPass()
    {
        $board = [
            "0,0" => [[0, "Q"]],
            "0,1" => [[1, "Q"]],
            "0,-1" => [[0, "B"]],
            "0,2" => [[1, "B"]],
            "-1,0" => [[0, "B"]],
            "1,1" => [[1, "B"]],
            "1,-1" => [[0, "S"]],
            "-1,2" => [[1, "S"]],
            "0,-2" => [[0, "S"]],
            "0,3" => [[1, "S"]],
            "-1,-1" => [[0, "A"]],
            "1,2" => [[1, "A"]],
            "1,-2" => [[0, "A"]],
            "-1,3" => [[1, "A"]],
            "-2,0" => [[0, "A"]],
            "2,1" => [[1, "A"]],
            "-2,1" => [[0, "G"]],
            "2,0" => [[1, "G"]],
            "2,-2" => [[0, "G"]],
            "-2,3" => [[1, "G"]],
            "0,-3" => [[0, "G"]],
            "0,4" => [[1, "G"]],
        ];
        $hand = [
            "Q" => 0,
            "B" => 0,
            "S" => 0,
            "A" => 0,
            "G" => 0,
        ];
        $player = 0;
        $this->assertFalse(mayPass($board, $hand, $player));

        $board = [
            "0,0" => [[1, "G"]],
            "0,1" => [[0, "G"]],
            "0,-1" => [[1, "S"]],
            "0,2" => [[1, "A"]],
            "-1,0" => [[0, "S"]],
            "1,1" => [[1, "B"]],
            "1,-1" => [[0, "B"]],
            "-1,2" => [[0, "G"]],
            "0,-2" => [[0, "Q"]],
            "0,3" => [[0, "A"]],
            "-1,-1" => [[1, "A"]],
            "1,2" => [[1, "G"]],
            "1,-2" => [[1, "A"]],
            "-1,3" => [[0, "G"]],
            "-2,0" => [[1, "B"]],
            "2,1" => [[0, "A"]],
            "-2,1" => [[1, "G"]],
            "2,0" => [[0, "A"]],
            "2,-2" => [[1, "B"]],
            "-2,3" => [[0, "A"]],
            "0,-3" => [[1, "S"]],
            "0,4" => [[1, "Q"]],
        ];
        $player = 0;
        $this->assertTrue(mayPass($board, $hand, $player));
        
        $board = [
            "0,0" => [[1, "A"]],
            "0,1" => [[1, "B"]],
            "0,-1" => [[0, "S"]],
            "0,2" => [[0, "G"]],
            "-1,0" => [[0, "S"]],
            "1,1" => [[1, "G"]],
            "1,-1" => [[1, "G"]],
            "-1,2" => [[1, "A"]],
            "0,-2" => [[1, "S"]],
            "0,3" => [[0, "G"]],
            "-1,-1" => [[0, "A"]],
            "1,2" => [[1, "B"]],
            "1,-2" => [[1, "Q"]],
            "-1,3" => [[0, "Q"]],
            "-2,0" => [[1, "B"]],
            "2,1" => [[0, "S"]],
            "-2,1" => [[0, "A"]],
            "2,0" => [[1, "G"]],
            "2,-2" => [[0, "Q"]],
            "-2,3" => [[1, "A"]],
            "0,-3" => [[0, "B"]],
            "0,4" => [[1, "S"]],
        ];
        $player = 1;
        $this->assertFalse(mayPass($board, $hand, $player));
    }

    public function testEndOfGame()
    {
        $board = [
            '0,0' => [[0, 'Q']],
            '0,1' => [[1, 'B']],
            '0,-1' => [[1, 'B']],
            '1,0' => [[1, 'B']],
            '-1,0' => [[1, 'B']],
            '1,-1' => [[1, 'B']],
            '-1,1' => [[1, 'B']],
        ];
        $this->assertEquals(endOfGame($board), 1);

        $board = [
            '0,0' => [[1, 'Q']],
            '0,1' => [[0, 'B']],
            '0,-1' => [[0, 'B']],
            '1,0' => [[0, 'B']],
            '-1,0' => [[0, 'B']],
            '1,-1' => [[0, 'B']],
            '-1,1' => [[0, 'B']],
        ];
        $this->assertEquals(endOfGame($board), 0);

        $board = [
            '0,0' => [[0, 'Q']],
            '0,1' => [[1, 'Q']],
            '0,-1' => [[1, 'B']],
            '1,0' => [[1, 'B']],
            '-1,0' => [[1, 'B']],
            '1,-1' => [[1, 'B']],
            '-1,1' => [[1, 'B']],
            '1,1' => [[1, 'B']],
            '-1,2' => [[1, 'B']],
            '0,2' => [[1, 'B']],
            '-2,2' => [[1, 'B']],
        ];
        $this->assertEquals(endOfGame($board), 2);

        $board = [
            '0,0' => [['0', 'A']],
            '0,1' => [['1', 'Q']],
            '0,2' => [['0', 'Q']],
            '1,1' => [['1', 'G']],
        ];
        $this->assertEquals(endOfGame($board), -1);

    }
}

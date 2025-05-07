<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../htdocs/main.inc.php';


class ProductBatchMovementTest  extends TestCase
{
    private $db;
    private $productBatch;

    protected function setUp(): void
    {
        // Mock the database connection
        $this->db = $this->createMock(Database::class);
        
        // Create an instance of the class that contains the method
        $this->productBatch = new ProductBatch($this->db);
    }

    public function testGetDateLastMovementProductBatch()
    {
        // Define the expected SQL query
        $expectedSql = "SELECT MAX(datem) as datem FROM ".MAIN_DB_PREFIX."stock_mouvement WHERE fk_product = 1 AND fk_entrepot = 2 AND batch = 'batch123'";

        // Mock the query method to return a result
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetch_object')->willReturn((object) ['datem' => '2025-05-06']);
        $resultMock->method('num_rows')->willReturn(1);

        $this->db->method('query')->with($expectedSql)->willReturn($resultMock);

        // Call the method and assert the result
        $date = $this->productBatch->getDateLastMovementProductBatch(2, 1, 'batch123');
        $this->assertEquals('2025-05-06', $date);
    }

    public function testGetDateLastMovementProductBatchNoResult()
    {
        // Define the expected SQL query
        $expectedSql = "SELECT MAX(datem) as datem FROM ".MAIN_DB_PREFIX."stock_mouvement WHERE fk_product = 1 AND fk_entrepot = 2 AND batch = 'batch123'";

        // Mock the query method to return no result
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('num_rows')->willReturn(0);

        $this->db->method('query')->with($expectedSql)->willReturn($resultMock);

        // Call the method and assert the result
        $date = $this->productBatch->getDateLastMovementProductBatch(2, 1, 'batch123');
        $this->assertEquals('', $date);
    }

    public function testGetDateLastMovementProductBatchQueryError()
    {
        // Define the expected SQL query
        $expectedSql = "SELECT MAX(datem) as datem FROM ".MAIN_DB_PREFIX."stock_mouvement WHERE fk_product = 1 AND fk_entrepot = 2 AND batch = 'batch123'";

        // Mock the query method to return false (indicating an error)
        $this->db->method('query')->with($expectedSql)->willReturn(false);

        // Call the method and assert the result
        $date = $this->productBatch->getDateLastMovementProductBatch(2, 1, 'batch123');
        $this->assertEquals('', $date);
    }
}

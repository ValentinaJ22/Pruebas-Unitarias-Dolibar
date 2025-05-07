<?php

// Sólo carga el bootstrap y luego tu test:
require_once __DIR__ . '/bootstrap.php';

use PHPUnit\Framework\TestCase;

class ReverseMouvementTest extends TestCase {
    // … tu setUp() y tests sin cambios …

    protected $db;
    protected $mouvement;

    protected function setUp(): void {
        
        // Configurar el mock de la base de datos
        $this->db = $this->getMockBuilder('DoliDBMysqli')
                          ->disableOriginalConstructor()
                          ->getMock();

        // Configurar el objeto de la clase que contiene la función reverseMouvement
        $this->mouvement = new Mouvement($this->db);
        $this->mouvement->id = 1;
        $this->mouvement->datem = time();
        $this->mouvement->label = '';
        $this->mouvement->inventorycode = '';
    }

    //Prueba que no se pueda revertir un movimiento que ya es una anulación.
    public function testReverseMouvementWithAnnulationLabel() {
        $this->mouvement->label = 'Annulation movement ID1';
        $result = $this->mouvement->reverseMouvement();
        $this->assertEquals(-1, $result);
    }

    //Prueba que no se revierta un movimiento que ya fue revertido antes.
    public function testReverseMouvementWithFormattedDate() {
        $formattedDate = "REVERTMV" . date('YmdHis', $this->mouvement->datem);
        $this->mouvement->inventorycode = $formattedDate;
        $result = $this->mouvement->reverseMouvement();
        $this->assertEquals(-1, $result);
    }

    //Prueba que la reversión del movimiento funcione correctamente cuando no hay errores.
    public function testReverseMouvementSuccess() {
        $this->db->method('query')->willReturn(true);
        $this->db->expects($this->once())->method('commit');

        $result = $this->mouvement->reverseMouvement();
        $this->assertEquals(1, $result);
    }

    //Prueba que, si hay un error en la base de datos, se haga rollback y no se complete la reversión.
    public function testReverseMouvementFailure() {
        $this->db->method('query')->willReturn(false);
        $this->db->expects($this->once())->method('rollback');

        $result = $this->mouvement->reverseMouvement();
        $this->assertEquals(-1, $result);
    }
}
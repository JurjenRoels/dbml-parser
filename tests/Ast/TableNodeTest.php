<?php
declare(strict_types=1);

namespace Butschster\Tests\Ast;

use Butschster\Dbml\Ast\TableNode;
use Butschster\Dbml\Exceptions\ColumnNotFoundException;

class TableNodeTest extends TestCase
{
    private TableNode $node;

    protected function setUp(): void
    {
        parent::setUp();

        $this->node = $this->parser->parse(<<<DBML
Table users as U {
    id int [pk, unique, increment, note: 'hello world'] // auto-increment
    full_name varchar(150) [not null, unique, default: 1, ref: > profiles.id]
    created_at timestamp
    country_code int
    type int
    note int
    Note: 'khong hieu duoc'

    indexes {
        id [name: 'created_at_index', note: 'Date', type: hash, pk]
        (id, name)
    }
}
DBML
        )->getTables()['users'];

    }

    function test_fields_should_be_parsed()
    {
        $this->assertCount(6, $this->node->getColumns());
    }

    function test_indexed_should_be_parsed()
    {
        $this->assertCount(2, $this->node->getIndexes());
    }

    function test_non_exists_column_should_throw_an_exception()
    {
        $this->expectException(ColumnNotFoundException::class);
        $this->expectErrorMessage("Column [not_exists] not found.");
        $this->node->getColumn('not_exists');
    }

    function test_gets_id_column()
    {
        // id int [pk, unique, increment, note: 'hello world'] // auto-increment
        $column = $this->node->getColumn('id');

        $this->assertEquals('id', $column->getName());
        $this->assertEquals('int', $column->getType()->getName());
        $this->assertEquals('hello world', $column->getNote());
        $this->assertNull($column->getDefault());
        $this->assertNull($column->getType()->getSize());
        $this->assertTrue($column->isPrimaryKey());
        $this->assertTrue($column->isUnique());
        $this->assertTrue($column->isIncrement());
        $this->assertFalse($column->isNull());
    }

    function test_gets_full_name_column()
    {
        // full_name varchar(150) [not null, unique, default: 1, ref: > profiles.id]
        $column = $this->node->getColumn('full_name');

        $this->assertEquals('full_name', $column->getName());
        $this->assertEquals('varchar', $column->getType()->getName());
        $this->assertEquals(150, $column->getType()->getSize());

        $this->assertNull($column->getNote());
        $this->assertSame(1, $column->getDefault()->getValue());
        $this->assertFalse($column->isPrimaryKey());
        $this->assertTrue($column->isUnique());
        $this->assertFalse($column->isIncrement());
        $this->assertFalse($column->isNull());
    }

    function test_gets_created_at_column()
    {
        // created_at timestamp
        $column = $this->node->getColumn('created_at');

        $this->assertEquals('created_at', $column->getName());
        $this->assertEquals('timestamp', $column->getType()->getName());
        $this->assertNull($column->getType()->getSize());

        $this->assertNull($column->getNote());
        $this->assertNull($column->getDefault());
        $this->assertFalse($column->isPrimaryKey());
        $this->assertFalse($column->isUnique());
        $this->assertFalse($column->isIncrement());
        $this->assertFalse($column->isNull());
    }

    function test_gets_name()
    {
        var_dump($this->node);
        $this->assertEquals('users', $this->node->getName());
    }

    function test_gets_alias()
    {
        $this->assertEquals('U', $this->node->getAlias());
    }

    function test_gets_note()
    {
        $this->assertEquals('khong hieu duoc', $this->node->getNote());
    }

    function test_gets_columns()
    {
        $this->assertCount(6, $this->node->getColumns());
    }

    function test_gets_indexes()
    {
        $this->assertCount(2, $this->node->getIndexes());
    }

    function test_gets_id_index()
    {
        // id [name: 'created_at_index', note: 'Date', type: hash, pk]
        $index = $this->node->getIndexes()[0];

        $this->assertCount(1, $index->getFields());
        $this->assertEquals('id', $index->getFields()[0]->getValue());
        $this->assertCount(4, $index->getSettings());

        $this->assertTrue($index->isPrimaryKey());
        $this->assertFalse($index->isUnique());
        $this->assertEquals('created_at_index', $index->getName());
        $this->assertEquals('Date', $index->getNote());
    }

    function test_gets_id_name_index()
    {
        // (id, name)
        $index = $this->node->getIndexes()[1];

        $this->assertCount(2, $index->getFields());
        $this->assertEquals('id', $index->getFields()[0]->getValue());
        $this->assertEquals('name', $index->getFields()[1]->getValue());
        $this->assertCount(0, $index->getSettings());

        $this->assertFalse($index->isPrimaryKey());
        $this->assertFalse($index->isUnique());
        $this->assertNull($index->getName());
        $this->assertNull($index->getNote());
    }
}

<?php
namespace Cerad\ProjectBundle;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit_Framework_TestCase;
use Symfony\Component\VarDumper\VarDumper;

class ProjectBundleTest extends PHPUnit_Framework_TestCase
{
    public function test1()
    {
        $this->assertTrue(true);

        $config = new Configuration();

        $connParams = array(
            'dbname'   => 'projects',
            'user'     => 'root',
            'memory'   => true,
            'driver'   => 'pdo_sqlite',
        );

        $conn = DriverManager::getConnection($connParams,$config);

        $this->assertInstanceOf(Connection::class,$conn);

        // Not supported by sqllite
        //$conn->exec('CREATE DATABASE projects');
        //$conn->exec('USE             projects');

        $sql = <<<EOD
CREATE TABLE games
(
  gameId     VARCHAR(99) NOT NULL,
  projectId  VARCHAR(99) NOT NULL,
  gameNumber INTEGER     NOT NULL,
  role       VARCHAR(40) NOT NULL DEFAULT 'game', -- kftm, scrimmage
  fieldName  VARCHAR(99),
  venueName  VARCHAR(99),

  start   DATETIME,
  finish  DATETIME,

  state   VARCHAR(40) NOT NULL DEFAULT 'Published',
  status  VARCHAR(40) NOT NULL DEFAULT 'Normal',

  reportText  LONGTEXT,
  reportState VARCHAR(40) NOT NULL DEFAULT 'Initial',

  CONSTRAINT games_primaryKey PRIMARY KEY(gameId),

  CONSTRAINT games_unique_gameNumber UNIQUE(projectId,gameNumber)

);
EOD;

        $conn->exec($sql);
        $conn->insert('games',['projectId' => 'P1','gameNumber'=>666,'gameId'=>'P1:666']);

        $stmt = $conn->executeQuery('SELECT count(*) AS count FROM games');
        $row = $stmt->fetch();
        $this->assertEquals(1,$row['count']);

        $schema = new Schema();
        $projectTable = $schema->createTable('projects');
        $projectTable->addColumn('projectId','string',['length'=>99,'notnull'=>true]);
        $projectTable->addColumn('title','string',    ['length'=>99]);

        $projectTable->setPrimaryKey(['projectId']);

        $queries = $schema->toSql($conn->getDatabasePlatform());
        VarDumper::dump($queries);
        foreach($queries as $sql) {
            $conn->exec($sql);
        }
        $conn->insert('projects',['projectId' => 'P1','title'=>'P1 Title']);
        $stmt = $conn->executeQuery('SELECT * FROM projects WHERE projectId = ?',['P1']);
        $row = $stmt->fetch();
        //VarDumper::dump($row);
        $this->assertEquals('P1 Title',$row['title']);


    }
}
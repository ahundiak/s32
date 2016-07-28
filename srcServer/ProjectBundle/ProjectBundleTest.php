<?php
namespace Cerad\ProjectBundle;

use Cerad\Common\DatabaseSchema;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit_Framework_TestCase;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\Yaml\Yaml;

class ProjectBundleTest extends PHPUnit_Framework_TestCase
{
    private function createConnectionSqlite()
    {
        $config = new Configuration();

        $connParams = array(
            'dbname' => 'projects',
            'user' => 'root',
            'memory' => true,
            'driver' => 'pdo_sqlite',
        );

        return DriverManager::getConnection($connParams, $config);
    }

    private function createConnectionMysql()
    {
        $config = new Configuration();

        $connParams = array(
            'dbname' => 'projects_test',
            'user' => 'test',
            'password' => 'test',
            'driver' => 'pdo_mysql',
        );

        return DriverManager::getConnection($connParams, $config);
    }

    public function test1()
    {
        self::assertTrue(true);

        $conn = $this->createConnectionSqlite();

        self::assertInstanceOf(Connection::class, $conn);

        // Not supported by sqlite
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
        $conn->insert('games', ['projectId' => 'P1', 'gameNumber' => 666, 'gameId' => 'P1:666']);

        $stmt = $conn->executeQuery('SELECT count(*) AS count FROM games');
        $row = $stmt->fetch();
        $this->assertEquals(1, $row['count']);

        $schema = new Schema();
        $projectTable = $schema->createTable('projects');
        $projectTable->addColumn('projectId', 'string', ['length' => 99, 'notnull' => true]);
        $projectTable->addColumn('title', 'string', ['length' => 99]);

        $projectTable->setPrimaryKey(['projectId']);

        $queries = $schema->toSql($conn->getDatabasePlatform());

        foreach ($queries as $sql) {
            $conn->exec($sql);
        }
        $conn->insert('projects', ['projectId' => 'P1', 'title' => 'P1 Title']);
        $stmt = $conn->executeQuery('SELECT * FROM projects WHERE projectId = ?', ['P1']);
        $row = $stmt->fetch();

        $this->assertEquals('P1 Title', $row['title']);

        $conn->close();
    }

    public function test2()
    {
        $schemaPath = __DIR__.'/zconfig/schema.yml';
        $schemaData = Yaml::parse(file_get_contents($schemaPath));
        $schema = DatabaseSchema::createFromArray($schemaData);

        $queries = $schema->toSql(new MySqlPlatform());
        //VarDumper::dump($queries);
        $queries = $schema->toSql(new SqlitePlatform());
        //VarDumper::dump($queries);

        $conn = $this->createConnectionMysql();

        $conn->exec('DROP   DATABASE projects_test');
        $conn->exec('CREATE DATABASE projects_test');
        $conn->exec('USE             projects_test');

        $queries = $schema->toSql($conn->getDatabasePlatform());
        foreach ($queries as $query) {
            $conn->exec($query);
        }
        $projectId2012 = 'AYSONationalGames2012';
        $projectId2014 = 'AYSONationalGames2014';
        $projectId2016 = 'AYSONationalGames2016';

        $conn->insert('projects', ['projectId' => $projectId2012, 'projectIdView' => 'NG2012']);
        $conn->insert('projects', ['projectId' => $projectId2014, 'projectIdView' => 'NG2014']);
        $conn->insert('projects', ['projectId' => $projectId2016, 'projectIdView' => 'NG2016']);

        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2016, 'dateKey' => '2016-07-06', 'dateKeyView' => 'Wed']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2016, 'dateKey' => '2016-07-07', 'dateKeyView' => 'Thu']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2016, 'dateKey' => '2016-07-08', 'dateKeyView' => 'Fri']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2016, 'dateKey' => '2016-07-09', 'dateKeyView' => 'Sat']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2016, 'dateKey' => '2016-07-10', 'dateKeyView' => 'Sun']
        );

        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2014, 'dateKey' => '2014-07-02', 'dateKeyView' => 'Wed']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2014, 'dateKey' => '2014-07-03', 'dateKeyView' => 'Thu']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2014, 'dateKey' => '2014-07-04', 'dateKeyView' => 'Fri']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2014, 'dateKey' => '2014-07-05', 'dateKeyView' => 'Sat']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2014, 'dateKey' => '2014-07-06', 'dateKeyView' => 'Sun']
        );

        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2012, 'dateKey' => '2012-07-02', 'dateKeyView' => 'Wed']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2012, 'dateKey' => '2012-07-03', 'dateKeyView' => 'Thu']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2012, 'dateKey' => '2012-07-04', 'dateKeyView' => 'Fri']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2012, 'dateKey' => '2012-07-05', 'dateKeyView' => 'Sat']
        );
        $conn->insert(
            'projectDates',
            ['projectId' => $projectId2012, 'dateKey' => '2012-07-06', 'dateKeyView' => 'Sun']
        );

        $stmt = $conn->executeQuery('SELECT * FROM projectDates WHERE projectId = ?', [$projectId2016]);
        $rows = $stmt->fetchAll();
        $this->assertCount(5, $rows);

        // The choices
        $projectFinder = new ProjectFinder($conn);
        $projectChoices = $projectFinder->findProjectChoices();
        self::assertCount(3, $projectChoices);
        $projectIds = array_keys($projectChoices);
        self::assertEquals($projectId2012, $projectIds[2]);

        $projectDateChoices = $projectFinder->findProjectDateChoices($projectId2016);
        self::assertCount(5, $projectDateChoices);
        $projectDateValues = array_values($projectDateChoices);
        self::assertEquals('Thu', $projectDateValues[1]);

        // Some levels
        $conn->insert(
            'projectLevels',
            [
                'projectId' => $projectId2014,
                'levelKey' => 'VIP',
                'levelKeyView' => 'VIP',
                'programKey' => 'VIP',
                'programKeyView' => 'VIP',
            ]
        );
        $conn->insert(
            'projectLevels',
            [
                'projectId' => $projectId2014,
                'levelKey' => 'U10BCore',
                'levelKeyView' => 'U10-B Core',
                'programKey' => 'Core',
                'programKeyView' => 'Core',
            ]
        );
        $conn->insert(
            'projectLevels',
            [
                'projectId' => $projectId2014,
                'levelKey' => 'U10GCore',
                'levelKeyView' => 'U10-G Core',
                'programKey' => 'Core',
                'programKeyView' => 'Core',
            ]
        );
        $conn->insert(
            'projectLevels',
            [
                'projectId' => $projectId2014,
                'levelKey' => 'U10BExtra',
                'levelKeyView' => 'U10-B Extra',
                'programKey' => 'Extra',
                'programKeyView' => 'Extra',
            ]
        );
        $conn->insert(
            'projectLevels',
            [
                'projectId' => $projectId2014,
                'levelKey' => 'U10GExtra',
                'levelKeyView' => 'U10-G Extra',
                'programKey' => 'Extra',
                'programKeyView' => 'Extra',
            ]
        );

        $projectLevelChoices = $projectFinder->findProjectLevelChoices($projectId2014);
        self::assertCount(5, $projectLevelChoices);
        self::assertEquals('U10-G Extra', $projectLevelChoices['U10GExtra']);

        $projectProgramChoices = $projectFinder->findProjectProgramChoices($projectId2014);
        self::assertCount(3, $projectProgramChoices);
        self::assertEquals('Extra', $projectProgramChoices['Extra']);

        // Test cascade delete
        $conn->delete('projects', ['projectId' => $projectId2016]);
        $stmt = $conn->executeQuery('SELECT * FROM projectDates WHERE projectId = ?', [$projectId2016]);
        $rows = $stmt->fetchAll();
        $this->assertCount(0, $rows);

        $stmt = $conn->executeQuery('SELECT * FROM projects');
        $rows = $stmt->fetchAll();
        $this->assertCount(2, $rows);

        // Test cascade update
        $projectId2014x = $projectId2014.'x';
        $conn->update('projects', ['projectId' => $projectId2014x], ['projectId' => $projectId2014]);
        $stmt = $conn->executeQuery('SELECT * FROM projectDates WHERE projectId = ?', [$projectId2014x]);
        $rows = $stmt->fetchAll();
        $this->assertCount(5, $rows);
    }
}
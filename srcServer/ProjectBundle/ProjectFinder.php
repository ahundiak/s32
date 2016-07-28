<?php
namespace Cerad\ProjectBundle;

use Doctrine\DBAL\Connection;

class ProjectFinder
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function findProjectChoices()
    {
        $sql = 'SELECT projectId,projectIdView FROM projects ORDER BY projectId DESC';
        $stmt = $this->conn->executeQuery($sql);
        $choices = [];
        while ($row = $stmt->fetch()) {
            $choices[$row['projectId']] = $row['projectIdView'];
        }

        return $choices;
    }

    public function findProjectDateChoices($projectId)
    {
        $sql = 'SELECT dateKey,dateKeyView FROM projectDates WHERE projectId = ? ORDER BY dateKey ASC';
        $stmt = $this->conn->executeQuery($sql, [$projectId]);
        $choices = [];
        while ($row = $stmt->fetch()) {
            $choices[$row['dateKey']] = $row['dateKeyView'];
        }

        return $choices;
    }

    public function findProjectLevelChoices($projectId)
    {
        $sql = 'SELECT levelKey,levelKeyView FROM projectLevels WHERE projectId = ? ORDER BY levelKey ASC';
        $stmt = $this->conn->executeQuery($sql, [$projectId]);
        $choices = [];
        while ($row = $stmt->fetch()) {
            $choices[$row['levelKey']] = $row['levelKeyView'];
        }

        return $choices;
    }

    public function findProjectProgramChoices($projectId)
    {
        $sql = 'SELECT DISTINCT programKey,programKeyView FROM projectLevels WHERE projectId = ? ORDER BY programKey ASC';
        $stmt = $this->conn->executeQuery($sql, [$projectId]);
        $choices = [];
        while ($row = $stmt->fetch()) {
            $choices[$row['programKey']] = $row['programKeyView'];
        }

        return $choices;
    }
}
<?php

class IssuesDB extends SQLite3 {
  private $db;

  function __construct($dir) {
    $file = $dir . '/qa_catalogue.sqlite';
    $this->open($file);
  }

  public function getByCategoryAndType($categoryId, $typeId, $order = 'records DESC', $offset = 0, $limit) {
    $default_order = 'records DESC';
    if (!preg_match('/^(MarcPath|message|instances|records) (ASC|DESC)$/', $order))
      $order = $default_order;
    $stmt = $this->prepare('SELECT *
       FROM issue_summary
       WHERE categoryId = :categoryId AND typeId = :typeId
       ORDER BY ' . $order . ' 
       LIMIT :limit
       OFFSET :offset
    ');
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);
    $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);

    return $stmt->execute();
  }

  public function getByCategoryAndTypeCount($categoryId, $typeId) {
    $stmt = $this->prepare('SELECT COUNT(*) AS count
       FROM issue_summary
       WHERE categoryId = :categoryId AND typeId = :typeId
    ');
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);

    return $stmt->execute();
  }

  public function getByCategoryTypeAndPath($categoryId, $typeId, $path = null, $order = 'records DESC', $offset = 0, $limit) {
    $default_order = 'records DESC';
    if (!preg_match('/^(MarcPath|message|instances|records) (ASC|DESC)$/', $order))
      $order = $default_order;
    $stmt = $this->prepare('SELECT *
       FROM issue_summary
       WHERE categoryId = :categoryId AND typeId = :typeId AND MarcPath = :path
       ORDER BY ' . $order . '
       LIMIT :limit
       OFFSET :offset
    ');
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);
    $stmt->bindValue(':path', $path, SQLITE3_TEXT);
    $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);

    return $stmt->execute();
  }

  public function getByCategoryTypeAndPathCount($categoryId, $typeId, $path) {
    $stmt = $this->prepare('SELECT COUNT(*) AS count
       FROM issue_summary
       WHERE categoryId = :categoryId AND typeId = :typeId AND MarcPath = :path
    ');
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);
    $stmt->bindValue(':path', $path, SQLITE3_TEXT);

    return $stmt->execute();
  }

  public function getByCategoryAndTypeGrouppedByPath($categoryId, $typeId, $order = 'records DESC', $offset = 0, $limit) {
    $default_order = 'records DESC';
    if (!preg_match('/^(path|variants|instances|records) (ASC|DESC)$/', $order))
      $order = $default_order;
    $stmt = $this->prepare('SELECT path, variants, instances, records
FROM issue_groups AS s
WHERE categoryId = :categoryId AND typeId = :typeId
ORDER BY ' . $order . ';');
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);

    return $stmt->execute();
  }

  public function getByCategoryAndTypeGrouppedByPathCount($categoryId, $typeId) {
    $stmt = $this->prepare(
      'SELECT COUNT(*) AS count
      FROM issue_groups AS s
      WHERE categoryId = :categoryId AND typeId = :typeId'
    );
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);

    return $stmt->execute();
  }

  public function getRecordIdsByErrorIdCount($errorId) {
    $stmt = $this->prepare('SELECT COUNT(distinct(id)) AS count FROM issue_details WHERE errorId = :errorId;');
    $stmt->bindValue(':errorId', $errorId, SQLITE3_INTEGER);
    error_log(preg_replace('/[\s\n]+/', ' ', $stmt->getSQL(true)));

    return $stmt->execute();
  }

  public function getRecordIdsByErrorId($errorId, $offset = 0, $limit = -1) {
    $sql = 'SELECT distinct(id) FROM issue_details WHERE errorId = :id';
    return $this->getRecordIdsById($sql, $errorId, $offset, $limit);
  }

  public function getRecordIdsByCategoryIdCount($categoryId) {
    $stmt = $this->prepare(
      'SELECT COUNT(distinct(id)) AS count
       FROM issue_details
       WHERE errorId IN 
            (SELECT distinct(id) FROM issue_summary WHERE categoryId = :categoryId)');
    $stmt->bindValue(':categoryId', $categoryId, SQLITE3_INTEGER);
    error_log(preg_replace('/[\s\n]+/', ' ', $stmt->getSQL(true)));

    return $stmt->execute();
  }

  public function getRecordIdsByCategoryId($categoryId, $offset = 0, $limit = -1) {
    $sql = 'SELECT distinct(id)
       FROM issue_details
       WHERE errorId IN 
            (SELECT distinct(id) FROM issue_summary WHERE categoryId = :id)';
    return $this->getRecordIdsById($sql, $categoryId, $offset, $limit);
  }

  public function getRecordIdsByTypeIdCount($typeId) {
    $stmt = $this->prepare(
      'SELECT COUNT(distinct(id)) AS count
       FROM issue_details
       WHERE errorId IN 
            (SELECT distinct(id) FROM issue_summary WHERE typeId = :typeId);');
    $stmt->bindValue(':typeId', $typeId, SQLITE3_INTEGER);
    error_log(preg_replace('/[\s\n]+/', ' ', $stmt->getSQL(true)));

    return $stmt->execute();
  }

  public function getRecordIdsByTypeId($typeId, $offset = 0, $limit = -1) {
    $sql = 'SELECT distinct(id)
       FROM issue_details
       WHERE errorId IN 
            (SELECT distinct(id) FROM issue_summary WHERE typeId = :id)';
    return $this->getRecordIdsById($sql, $typeId, $offset, $limit);
  }

  private function getRecordIdsById($sql, $id, $offset = 0, $limit = -1) {
    if ($limit != -1) {
      $sql .= ' LIMIT :limit OFFSET :offset';
    }
    $stmt = $this->prepare($sql);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    if ($limit != -1) {
      $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
      $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
    }
    error_log(preg_replace('/[\s\n]+/', ' ', $stmt->getSQL(true)));

    return $stmt->execute();
  }
}
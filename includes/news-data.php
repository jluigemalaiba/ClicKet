<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function clicketNewsEnsureSchema(): void {
    static $ready = false;
    if ($ready) {
        return;
    }

    clicketDbExecute(
        'CREATE TABLE IF NOT EXISTS news_articles (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            article_key VARCHAR(40) NOT NULL,
            title VARCHAR(130) NOT NULL,
            category ENUM("For Fans", "For Organizers", "Platform Updates") NOT NULL,
            description VARCHAR(360) NOT NULL,
            banner_filename VARCHAR(255) DEFAULT NULL,
            status ENUM("draft", "published", "archived") NOT NULL DEFAULT "draft",
            author_staff_id BIGINT UNSIGNED DEFAULT NULL,
            published_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_news_articles_article_key (article_key),
            KEY idx_news_articles_status_published_at (status, published_at),
            KEY idx_news_articles_author_staff_id (author_staff_id),
            CONSTRAINT fk_news_articles_author_staff
              FOREIGN KEY (author_staff_id) REFERENCES staff_accounts (id)
              ON UPDATE CASCADE ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    clicketDbExecute(
        'CREATE TABLE IF NOT EXISTS news_article_sections (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            article_id BIGINT UNSIGNED NOT NULL,
            section_order SMALLINT UNSIGNED NOT NULL,
            header VARCHAR(180) NOT NULL,
            content TEXT NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_news_article_sections_article_order (article_id, section_order),
            KEY idx_news_article_sections_article_id (article_id),
            CONSTRAINT fk_news_article_sections_article
              FOREIGN KEY (article_id) REFERENCES news_articles (id)
              ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ready = true;
}

function clicketNewsStatusLabel(string $status): string {
    return match (strtolower($status)) {
        'published' => 'Published',
        'archived' => 'Archived',
        default => 'Draft',
    };
}

function clicketNewsRows(string $where = '', array $params = []): array {
    clicketNewsEnsureSchema();

    $articles = clicketDbFetchAll(
        'SELECT na.*, sa.name AS author_name
         FROM news_articles na
         LEFT JOIN staff_accounts sa ON sa.id = na.author_staff_id'
        . $where .
        ' ORDER BY COALESCE(na.published_at, na.updated_at) DESC, na.id DESC',
        $params
    );
    if (!$articles) {
        return [];
    }

    $articleIds = array_map(static fn (array $article): int => (int) $article['id'], $articles);
    $placeholders = implode(',', array_fill(0, count($articleIds), '?'));
    $sectionRows = clicketDbExecute(
        'SELECT article_id, section_order, header, content
         FROM news_article_sections
         WHERE article_id IN (' . $placeholders . ')
         ORDER BY article_id, section_order',
        $articleIds
    )->fetchAll();
    $sectionsByArticle = [];
    foreach ($sectionRows as $section) {
        $sectionsByArticle[(int) $section['article_id']][] = [
            'header' => (string) $section['header'],
            'content' => (string) $section['content'],
        ];
    }

    return array_map(static function (array $article) use ($sectionsByArticle): array {
        $articleId = (int) $article['id'];
        return [
            'id' => (string) $article['article_key'],
            'title' => (string) $article['title'],
            'category' => (string) $article['category'],
            'description' => (string) $article['description'],
            'banner' => (string) ($article['banner_filename'] ?? ''),
            'sections' => $sectionsByArticle[$articleId] ?? [],
            'status' => clicketNewsStatusLabel((string) $article['status']),
            'author' => (string) ($article['author_name'] ?? 'Authorized Staff'),
            'created_at' => (string) $article['created_at'],
            'updated_at' => (string) $article['updated_at'],
            'published_at' => $article['published_at'] !== null ? (string) $article['published_at'] : null,
        ];
    }, $articles);
}

function clicketReadNews(): array {
    return clicketNewsRows();
}

function clicketPublishedNews(): array {
    return clicketNewsRows(' WHERE na.status = "published"');
}

function clicketCreateNewsArticle(array $article, int $authorStaffId): array {
    clicketNewsEnsureSchema();

    $status = strtolower((string) ($article['status'] ?? 'draft'));
    $articleKey = 'NWS-' . strtoupper(substr(hash('sha256', microtime(true) . '|' . ($article['title'] ?? '')), 0, 10));
    $pdo = clicketDb();
    $pdo->beginTransaction();
    try {
        clicketDbExecute(
            'INSERT INTO news_articles
               (article_key, title, category, description, banner_filename, status, author_staff_id, published_at)
             VALUES
               (:article_key, :title, :category, :description, :banner_filename, :status, :author_staff_id,
                CASE WHEN :published_status = "published" THEN UTC_TIMESTAMP() ELSE NULL END)',
            [
                'article_key' => $articleKey,
                'title' => (string) $article['title'],
                'category' => (string) $article['category'],
                'description' => (string) $article['description'],
                'banner_filename' => (string) ($article['banner'] ?? '') ?: null,
                'status' => $status,
                'author_staff_id' => $authorStaffId,
                'published_status' => $status,
            ]
        );
        $articleId = (int) $pdo->lastInsertId();
        foreach ((array) ($article['sections'] ?? []) as $index => $section) {
            clicketDbExecute(
                'INSERT INTO news_article_sections (article_id, section_order, header, content)
                 VALUES (:article_id, :section_order, :header, :content)',
                [
                    'article_id' => $articleId,
                    'section_order' => $index + 1,
                    'header' => (string) ($section['header'] ?? ''),
                    'content' => (string) ($section['content'] ?? ''),
                ]
            );
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }

    $rows = clicketNewsRows(' WHERE na.article_key = :article_key', ['article_key' => $articleKey]);
    return $rows[0] ?? [];
}

function clicketNewsDate(string $date): string {
    $timestamp = strtotime($date);
    return $timestamp ? date('F j, Y \a\t g:i A', $timestamp) : 'Not published';
}

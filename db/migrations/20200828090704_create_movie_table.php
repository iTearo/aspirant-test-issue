<?php

/**
 * @noinspection AutoloadingIssuesInspection
 */

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateMovieTable extends AbstractMigration
{
    public function up(): void
    {
        $this->query(/** @lang SQL */ 'BEGIN;');

        $this->query(/** @lang SQL */ <<<SQL
            CREATE TABLE `movie` (
                id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(180) NOT NULL,
                link VARCHAR(180) NOT NULL,
                description TEXT NOT NULL,
                pub_date DATETIME NOT NULL, 
                image VARCHAR(180) NULL, 
                INDEX title (title), 
                PRIMARY KEY(id)
            ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
        SQL);

        $this->query(/** @lang SQL */ 'COMMIT;');
    }

    public function down(): void
    {
        $this->query(/** @lang SQL */ 'BEGIN;');

        $this->query(/** @lang SQL */ 'DROP TABLE `movie`;');

        $this->query(/** @lang SQL */ 'COMMIT;');
    }
}

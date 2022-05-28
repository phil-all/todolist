<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration
 */
final class Version20220527164100 extends AbstractMigration
{
    /**
     * @see AbstractMigration
     */
    public function getDescription(): string
    {
        return 'User and Task tables creation';
    }

    /**
     * @see AbstractMigration
     */
    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE task (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                title VARCHAR(255) NOT NULL,
                content LONGTEXT NOT NULL,
                is_done TINYINT(1) NOT NULL,
                INDEX IDX_527EDB25A76ED395 (user_id),
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB'
        );

        $this->addSql(
            'CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL,
                username VARCHAR(25) NOT NULL,
                password VARCHAR(64) NOT NULL,
                email VARCHAR(60) NOT NULL,
                roles JSON NOT NULL,
                UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
                UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4
            COLLATE `utf8mb4_unicode_ci`
            ENGINE = InnoDB'
        );

        $this->addSql(
            'ALTER TABLE task
            ADD CONSTRAINT FK_527EDB25A76ED395
            FOREIGN KEY (user_id)
            REFERENCES user (id)'
        );
    }

    /**
     * @see AbstractMigration
     */
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25A76ED395');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user');
    }
}

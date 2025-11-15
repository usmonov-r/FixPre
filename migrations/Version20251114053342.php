<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114053342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'passwordResetToken and  passwordResetExpiresAt properties created to User entity to implement forgot password feature';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD password_reset_token VARCHAR(500) DEFAULT NULL, ADD password_reset_expires_at DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP password_reset_token, DROP password_reset_expires_at
        SQL);
    }
}

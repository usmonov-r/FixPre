<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106131030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'feat: user property added as a MTO relationship to FeedbackResult entity';

    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback_result ADD CONSTRAINT FK_2E26F55DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2E26F55DA76ED395 ON feedback_result (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback_result DROP FOREIGN KEY FK_2E26F55DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2E26F55DA76ED395 ON feedback_result
        SQL);
    }
}

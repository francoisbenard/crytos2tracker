<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220408115809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mycrypto ADD crypto_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mycrypto ADD CONSTRAINT FK_8ED676DDE9571A63 FOREIGN KEY (crypto_id) REFERENCES cryptolist (id)');
        $this->addSql('CREATE INDEX IDX_8ED676DDE9571A63 ON mycrypto (crypto_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mycrypto DROP FOREIGN KEY FK_8ED676DDE9571A63');
        $this->addSql('DROP INDEX IDX_8ED676DDE9571A63 ON mycrypto');
        $this->addSql('ALTER TABLE mycrypto DROP crypto_id');
    }
}

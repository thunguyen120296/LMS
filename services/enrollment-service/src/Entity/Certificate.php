<?php

declare(strict_types=1);

namespace App\Enrollment\Entity;

use App\Enrollment\Repository\CertificateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
#[ORM\Table(name: 'certificates', schema: 'enrollment')]
#[ORM\Index(columns: ['user_id'], name: 'idx_enrollment_cert_user')]
#[ORM\Index(columns: ['course_id'], name: 'idx_enrollment_cert_course')]
#[ORM\HasLifecycleCallbacks]
class Certificate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\OneToOne(targetEntity: Enrollment::class, inversedBy: 'certificate')]
    #[ORM\JoinColumn(name: 'enrollment_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Enrollment $enrollment;

    /** Denormalized for quick lookup without joining enrollment */
    #[ORM\Column(type: 'uuid')]
    private string $userId;

    #[ORM\Column(type: 'uuid')]
    private string $courseId;

    /** Human-readable unique number e.g. UC-2024-00001234 */
    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $certificateNumber;

    /** URL of the certificate template (SVG/HTML) in storage */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $templateUrl = null;

    /** URL of the generated PDF in CDN/storage */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $issuedPdfUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $issuedAt;

    public function __construct(
        Enrollment $enrollment,
        string $userId,
        string $courseId,
        string $certificateNumber,
    ) {
        $this->enrollment        = $enrollment;
        $this->userId            = $userId;
        $this->courseId          = $courseId;
        $this->certificateNumber = $certificateNumber;
        $this->issuedAt          = new \DateTimeImmutable();
    }

    public function attachPdf(string $pdfUrl): void
    {
        $this->issuedPdfUrl = $pdfUrl;
    }

    public function getId(): string { return $this->id; }
    public function getEnrollment(): Enrollment { return $this->enrollment; }
    public function getUserId(): string { return $this->userId; }
    public function getCourseId(): string { return $this->courseId; }
    public function getCertificateNumber(): string { return $this->certificateNumber; }
    public function getTemplateUrl(): ?string { return $this->templateUrl; }
    public function setTemplateUrl(?string $url): static { $this->templateUrl = $url; return $this; }
    public function getIssuedPdfUrl(): ?string { return $this->issuedPdfUrl; }
    public function getIssuedAt(): \DateTimeImmutable { return $this->issuedAt; }
}
<?php

declare(strict_types=1);

namespace App\Course\Entity;

use App\Course\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

// ============================================================
// Tag
// ============================================================

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags', schema: 'course')]
class Tag
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 120, unique: true)]
    private string $slug;

    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: CourseTag::class, cascade: ['remove'])]
    private Collection $courseTags;

    public function __construct()
    {
        $this->courseTags = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    public function getCourseTags(): Collection { return $this->courseTags; }
}
<?php

declare(strict_types=1);

namespace App\Course\Entity;

use App\Course\Repository\CourseTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseTagRepository::class)]
#[ORM\Table(name: 'course_tags', schema: 'course')]
#[ORM\UniqueConstraint(name: 'uq_course_tag', columns: ['course_id', 'tag_id'])]
class CourseTag
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'courseTags')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Course $course;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'courseTags')]
    #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Tag $tag;

    public function __construct(Course $course, Tag $tag)
    {
        $this->course = $course;
        $this->tag    = $tag;
    }

    public function getCourse(): Course { return $this->course; }
    public function getTag(): Tag { return $this->tag; }
}
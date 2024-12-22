<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Travel>
     */
    #[ORM\OneToMany(targetEntity: Travel::class, mappedBy: 'destination', orphanRemoval: true)]
    private Collection $travel;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'country', orphanRemoval: true)]
    private Collection $activities;

    /**
     * @var Collection<int, language>
     */
    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'countries')]
    private Collection $languages;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currency = null;

    #[ORM\Column(length: 255)]
    private ?string $preposition = null;

    public function __construct()
    {
        $this->travel = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->languages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Travel>
     */
    public function getTravel(): Collection
    {
        return $this->travel;
    }

    public function addTravel(Travel $travel): static
    {
        if (!$this->travel->contains($travel)) {
            $this->travel->add($travel);
            $travel->setDestination($this);
        }

        return $this;
    }

    public function removeTravel(Travel $travel): static
    {
        if ($this->travel->removeElement($travel)) {
            // set the owning side to null (unless already changed)
            if ($travel->getDestination() === $this) {
                $travel->setDestination(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setCountry($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getCountry() === $this) {
                $activity->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    public function removeLanguage(language $language): static
    {
        $this->languages->removeElement($language);

        return $this;
    }

    public function getCurrency(): ?currency
    {
        return $this->currency;
    }

    public function setCurrency(?currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getPreposition(): ?string
    {
        return $this->preposition;
    }

    public function setPreposition(string $preposition): static
    {
        $this->preposition = $preposition;

        return $this;
    }
}

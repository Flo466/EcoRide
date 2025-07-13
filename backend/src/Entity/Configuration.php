<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'configurations')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    private ?User $user = null;

    /**
     * @var Collection<int, Parameter>
     */
    #[ORM\OneToMany(targetEntity: Parameter::class, mappedBy: 'configuration', orphanRemoval: true)]
    private Collection $parameters;

    public function __construct()
    {
        $this->parameters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Parameter>
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameter(Parameter $parameter): static
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters->add($parameter);
            $parameter->setConfiguration($this);
        }

        return $this;
    }

    public function removeParameter(Parameter $parameter): static
    {
        if ($this->parameters->removeElement($parameter)) {
            // set the owning side to null (unless already changed)
            if ($parameter->getConfiguration() === $this) {
                $parameter->setConfiguration(null);
            }
        }

        return $this;
    }
}

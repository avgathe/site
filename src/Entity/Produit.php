<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'l3_produits')]
#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    #[Assert\Positive(message: "Le prix unitaire doit être positif.")]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\Positive(message: "La quantité en stock doit être positive.")]
    private ?int $stock = null;


    /**
     * @var Collection<int, Pays>
     */
    #[ORM\ManyToMany(targetEntity: Pays::class, inversedBy: 'produits')]
    #[ORM\JoinTable(
        name: 'l3_produit_pays',
        joinColumns: [new ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'pays_id', referencedColumnName: 'id')]
    )]
    private Collection $pays;

    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'produit')]
    private Collection $paniers;


    public function __construct()
    {
        $this->pays = new ArrayCollection();
        $this->paniers = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {

        $this->libelle = $libelle;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, Pays>
     */
    public function getPays(): Collection
    {
        return $this->pays;
    }

    public function addPays(Pays $pays): static
    {
        if (!$this->pays->contains($pays)) {
            $this->pays[] = $pays;
        }

        return $this;
    }

    public function removePays(Pays $pays): static
    {
        $this->pays->removeElement($pays);
        return $this;
    }


    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function setPaniers(Collection $paniers): static
    {
        $this->paniers = $paniers;

        return $this;
    }
}

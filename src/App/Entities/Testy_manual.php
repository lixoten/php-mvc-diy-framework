<?php

declare(strict_types=1);

namespace App\Entities;

use App\Attributes\Field;

// Manually created Entity
class Testy
{
    private ?int $testyId = null;

    private ?int $testyStoreId = null;

    private int $testyUserId;

    private string $testyStatus;

    /**
     * @var string
     */
    private string $slug;

    private string $title;

    private string $content;

    private string $generic_text;

    private ?string $date_of_birth;

    private ?string $telephone;


    private ?string $gender_id;

    private ?string $gender_other;

    private bool $is_verified;



    private bool $interest_soccer_ind;

    private bool $interest_baseball_ind;

    private bool $interest_football_ind;

    private bool $interest_hockey_ind;

    private ?string $profile_picture = null;


    private ?string $createdAt = null;

    private ?string $updatedAt = null;

    private ?string $username = null;

    // Getters and setters~
    public function getTestyId(): ?int
    {
        return $this->testyId;
    }

    public function getRecordId(): ?int
    {
        return $this->testyId;
    }

    public function setTestyId(?int $testyId): self
    {
        $this->testyId = $testyId;
        return $this;
    }

    public function getTestyStoreId(): int
    {
        return $this->testyStoreId;
    }

    public function setTestyStoreId(?int $testyStoreId): self
    {
        $this->testyStoreId = $testyStoreId;
        return $this;
    }

    public function getTestyUserId(): int
    {
        return $this->testyUserId;
    }

    public function setTestyUserId(int $testyUserId): self
    {
        $this->testyUserId = $testyUserId;
        return $this;
    }

    public function getTestyStatus(): string
    {
        return $this->testyStatus;
    }

    public function setTestyStatus(string $testyStatus): self
    {
        $this->testyStatus = $testyStatus;
        return $this;
    }


    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getGenericText(): string
    {
        return $this->generic_text;
    }
    public function setGenericText(string $generic_text): self
    {
        $this->generic_text = $generic_text;
        return $this;
    }

    public function getDateOfBirth(): ?string
    {
        return $this->date_of_birth;
    }
    public function setDateOfBirth(?string $date_of_birth): self
    {
        $this->date_of_birth = $date_of_birth;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }



    public function getGenderId(): ?string
    {
        return $this->gender_id;
    }
    public function setGenderId(?string $gender_id): self
    {
        $this->gender_id = $gender_id;
        return $this;
    }

    public function getGenderOther(): ?string
    {
        return $this->gender_other;
    }
    public function setGenderOther(?string $gender_other): self
    {
        $this->gender_other = $gender_other;
        return $this;
    }







    public function getIsVerified(): bool
    {
        return $this->is_verified;
    }
    public function setIsVerified(int|bool $is_verified): self
    {
        $this->is_verified = (bool)$is_verified;
        return $this;
    }







    public function getInterestSoccerInd(): bool
    {
        return $this->interest_soccer_ind;
    }
    public function setInterestSoccerInd(int|bool $interest_soccer_ind): self
    {
        $this->interest_soccer_ind = (bool)$interest_soccer_ind;
        return $this;
    }

    public function getInterestBaseballInd(): bool
    {
        return $this->interest_baseball_ind;
    }
    public function setInterestBaseballInd(int|bool $interest_baseball_ind): self
    {
        $this->interest_baseball_ind = (bool)$interest_baseball_ind;
        return $this;
    }

    public function getInterestFootballInd(): bool
    {
        return $this->interest_football_ind;
    }
    public function setInterestFootballInd(int|bool $interest_football_ind): self
    {
        $this->interest_football_ind = (bool)$interest_football_ind;
        return $this;
    }


    public function getInterestHockeyInd(): bool
    {
        return $this->interest_hockey_ind;
    }
    public function setInterestHockeyInd(int|bool $interest_hockey_ind): self
    {
        $this->interest_hockey_ind = (bool)$interest_hockey_ind;
        return $this;
    }




    public function getProfilePicture(): ?string
    {
        return $this->profile_picture;
    }
    public function setProfilePicture(?string $profile_picture): self
    {
        $this->profile_picture = $profile_picture;
        return $this;
    }









    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->testyStatus === 'P';
    }
}

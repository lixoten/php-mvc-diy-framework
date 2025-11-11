<?php

declare(strict_types=1);

namespace App\Features\Testy;


/**
 * Generated File - Date: 20251109_203444
 * Entity class for Testy.
 *
 * @property-read array<string, mixed> $fields
 */
class Testy
{
    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var int
     */
    private ?int $store_id = null;

    /**
     * @var int
     */
    private int $user_id = 0;

    /**
     * @var string
     */
    private string $status = '';

    /**
     * @var string
     */
    private string $slug = '';

    /**
     * @var string
     */
    private string $title = '';

    /**
     * @var string
     */
    private ?string $content = null;

    /**
     * @var string
     */
    private ?string $generic_text = null;

    /**
     * @var int
     */
    private ?int $image_count = null;

    /**
     * @var int
     */
    private ?int $cover_image_id = null;

    /**
     * @var string
     */
    private ?string $date_of_birth = null;

    /**
     * @var string
     */
    private ?string $generic_date = null;

    /**
     * @var string
     */
    private ?string $generic_month = null;

    /**
     * @var string
     */
    private ?string $generic_week = null;

    /**
     * @var string
     */
    private ?string $generic_time = null;

    /**
     * @var string
     */
    private ?string $generic_datetime = null;

    /**
     * @var string
     */
    private ?string $telephone = null;

    /**
     * @var string
     */
    private ?string $gender_id = null;

    /**
     * @var string
     */
    private ?string $gender_other = null;

    /**
     * @var bool
     */
    private bool $is_verified = false;

    /**
     * @var bool
     */
    private bool $interest_soccer_ind = false;

    /**
     * @var bool
     */
    private bool $interest_baseball_ind = false;

    /**
     * @var bool
     */
    private bool $interest_football_ind = false;

    /**
     * @var bool
     */
    private bool $interest_hockey_ind = false;

    /**
     * @var string
     */
    private ?string $primary_email = null;

    /**
     * @var string
     */
    private ?string $secret_code_hash = null;

    /**
     * @var float
     */
    private float $balance = 0.0;

    /**
     * @var float
     */
    private ?float $generic_decimal = null;

    /**
     * @var int
     */
    private ?int $volume_level = null;

    /**
     * @var float
     */
    private ?float $start_rating = null;

    /**
     * @var int
     */
    private int $generic_number = 0;

    /**
     * @var int
     */
    private int $generic_num = 0;

    /**
     * @var string
     */
    private ?string $generic_color = null;

    /**
     * @var string
     */
    private ?string $wake_up_time = null;

    /**
     * @var string
     */
    private ?string $favorite_week_day = null;

    /**
     * @var string
     */
    private ?string $online_address = null;

    /**
     * @var string
     */
    private ?string $profile_picture = null;

    /**
     * @var string
     */
    private string $created_at = '';

    /**
     * @var string
     */
    private string $updated_at = '';
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getStoreId(): ?int
    {
        return $this->store_id;
    }

    /**
     * @param ?int $store_id
     * @return self
     */
    public function setStoreId(?int $store_id): self
    {
        $this->store_id = $store_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return self
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param ?string $content
     * @return self
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericText(): ?string
    {
        return $this->generic_text;
    }

    /**
     * @param ?string $generic_text
     * @return self
     */
    public function setGenericText(?string $generic_text): self
    {
        $this->generic_text = $generic_text;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getImageCount(): ?int
    {
        return $this->image_count;
    }

    /**
     * @param ?int $image_count
     * @return self
     */
    public function setImageCount(?int $image_count): self
    {
        $this->image_count = $image_count;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getCoverImageId(): ?int
    {
        return $this->cover_image_id;
    }

    /**
     * @param ?int $cover_image_id
     * @return self
     */
    public function setCoverImageId(?int $cover_image_id): self
    {
        $this->cover_image_id = $cover_image_id;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getDateOfBirth(): ?string
    {
        return $this->date_of_birth;
    }

    /**
     * @param ?string $date_of_birth
     * @return self
     */
    public function setDateOfBirth(?string $date_of_birth): self
    {
        $this->date_of_birth = $date_of_birth;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericDate(): ?string
    {
        return $this->generic_date;
    }

    /**
     * @param ?string $generic_date
     * @return self
     */
    public function setGenericDate(?string $generic_date): self
    {
        $this->generic_date = $generic_date;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericMonth(): ?string
    {
        return $this->generic_month;
    }

    /**
     * @param ?string $generic_month
     * @return self
     */
    public function setGenericMonth(?string $generic_month): self
    {
        $this->generic_month = $generic_month;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericWeek(): ?string
    {
        return $this->generic_week;
    }

    /**
     * @param ?string $generic_week
     * @return self
     */
    public function setGenericWeek(?string $generic_week): self
    {
        $this->generic_week = $generic_week;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericTime(): ?string
    {
        return $this->generic_time;
    }

    /**
     * @param ?string $generic_time
     * @return self
     */
    public function setGenericTime(?string $generic_time): self
    {
        $this->generic_time = $generic_time;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericDatetime(): ?string
    {
        return $this->generic_datetime;
    }

    /**
     * @param ?string $generic_datetime
     * @return self
     */
    public function setGenericDatetime(?string $generic_datetime): self
    {
        $this->generic_datetime = $generic_datetime;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * @param ?string $telephone
     * @return self
     */
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenderId(): ?string
    {
        return $this->gender_id;
    }

    /**
     * @param ?string $gender_id
     * @return self
     */
    public function setGenderId(?string $gender_id): self
    {
        $this->gender_id = $gender_id;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenderOther(): ?string
    {
        return $this->gender_other;
    }

    /**
     * @param ?string $gender_other
     * @return self
     */
    public function setGenderOther(?string $gender_other): self
    {
        $this->gender_other = $gender_other;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * @param bool $is_verified
     * @return self
     */
    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInterestSoccerInd(): bool
    {
        return $this->interest_soccer_ind;
    }

    /**
     * @param bool $interest_soccer_ind
     * @return self
     */
    public function setInterestSoccerInd(bool $interest_soccer_ind): self
    {
        $this->interest_soccer_ind = $interest_soccer_ind;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInterestBaseballInd(): bool
    {
        return $this->interest_baseball_ind;
    }

    /**
     * @param bool $interest_baseball_ind
     * @return self
     */
    public function setInterestBaseballInd(bool $interest_baseball_ind): self
    {
        $this->interest_baseball_ind = $interest_baseball_ind;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInterestFootballInd(): bool
    {
        return $this->interest_football_ind;
    }

    /**
     * @param bool $interest_football_ind
     * @return self
     */
    public function setInterestFootballInd(bool $interest_football_ind): self
    {
        $this->interest_football_ind = $interest_football_ind;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInterestHockeyInd(): bool
    {
        return $this->interest_hockey_ind;
    }

    /**
     * @param bool $interest_hockey_ind
     * @return self
     */
    public function setInterestHockeyInd(bool $interest_hockey_ind): self
    {
        $this->interest_hockey_ind = $interest_hockey_ind;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getPrimaryEmail(): ?string
    {
        return $this->primary_email;
    }

    /**
     * @param ?string $primary_email
     * @return self
     */
    public function setPrimaryEmail(?string $primary_email): self
    {
        $this->primary_email = $primary_email;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getSecretCodeHash(): ?string
    {
        return $this->secret_code_hash;
    }

    /**
     * @param ?string $secret_code_hash
     * @return self
     */
    public function setSecretCodeHash(?string $secret_code_hash): self
    {
        $this->secret_code_hash = $secret_code_hash;
        return $this;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     * @return self
     */
    public function setBalance(float $balance): self
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return ?float
     */
    public function getGenericDecimal(): ?float
    {
        return $this->generic_decimal;
    }

    /**
     * @param ?float $generic_decimal
     * @return self
     */
    public function setGenericDecimal(?float $generic_decimal): self
    {
        $this->generic_decimal = $generic_decimal;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getVolumeLevel(): ?int
    {
        return $this->volume_level;
    }

    /**
     * @param ?int $volume_level
     * @return self
     */
    public function setVolumeLevel(?int $volume_level): self
    {
        $this->volume_level = $volume_level;
        return $this;
    }

    /**
     * @return ?float
     */
    public function getStartRating(): ?float
    {
        return $this->start_rating;
    }

    /**
     * @param ?float $start_rating
     * @return self
     */
    public function setStartRating(?float $start_rating): self
    {
        $this->start_rating = $start_rating;
        return $this;
    }

    /**
     * @return int
     */
    public function getGenericNumber(): int
    {
        return $this->generic_number;
    }

    /**
     * @param int $generic_number
     * @return self
     */
    public function setGenericNumber(int $generic_number): self
    {
        $this->generic_number = $generic_number;
        return $this;
    }

    /**
     * @return int
     */
    public function getGenericNum(): int
    {
        return $this->generic_num;
    }

    /**
     * @param int $generic_num
     * @return self
     */
    public function setGenericNum(int $generic_num): self
    {
        $this->generic_num = $generic_num;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getGenericColor(): ?string
    {
        return $this->generic_color;
    }

    /**
     * @param ?string $generic_color
     * @return self
     */
    public function setGenericColor(?string $generic_color): self
    {
        $this->generic_color = $generic_color;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getWakeUpTime(): ?string
    {
        return $this->wake_up_time;
    }

    /**
     * @param ?string $wake_up_time
     * @return self
     */
    public function setWakeUpTime(?string $wake_up_time): self
    {
        $this->wake_up_time = $wake_up_time;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getFavoriteWeekDay(): ?string
    {
        return $this->favorite_week_day;
    }

    /**
     * @param ?string $favorite_week_day
     * @return self
     */
    public function setFavoriteWeekDay(?string $favorite_week_day): self
    {
        $this->favorite_week_day = $favorite_week_day;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getOnlineAddress(): ?string
    {
        return $this->online_address;
    }

    /**
     * @param ?string $online_address
     * @return self
     */
    public function setOnlineAddress(?string $online_address): self
    {
        $this->online_address = $online_address;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getProfilePicture(): ?string
    {
        return $this->profile_picture;
    }

    /**
     * @param ?string $profile_picture
     * @return self
     */
    public function setProfilePicture(?string $profile_picture): self
    {
        $this->profile_picture = $profile_picture;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     * @return self
     */
    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * @param string $updated_at
     * @return self
     */
    public function setUpdatedAt(string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}

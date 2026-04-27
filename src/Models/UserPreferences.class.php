<?php

namespace Src\Models;

class UserPreferences extends BaseModel
{
    protected static string $table = 'user_preferences';
    private int $user_id;
    private string $key_name;
    private string $value;
    private string $updated_at;

    public function getColNames(): array
    {
        return [
            'user_id',
            'key_name',
            'value',
            'updated_at'
        ];
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getKey_name()
    {
        return $this->key_name;
    }

    public function setKey_name($key_name)
    {
        $this->key_name = $key_name;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    public function setUpdated_at($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}

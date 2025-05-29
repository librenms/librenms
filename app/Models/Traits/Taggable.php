<?php

namespace App\Models\Traits;

use App\Models\Tag;
use App\Models\TagKey;

trait Taggable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Tag, $this>
     */
    public function tags()
    {
        return $this->hasMany(Tag::class, 'object_id', $this->getKeyName())
            ->where('object_type', static::class);
    }

    /**
     * Get all tags for this model as key=>value array, or a specific tags if $key is provided
     *
     * @param  array|string|null  $key
     */
    public function getTag($key = null)
    {
        $tags = $this->tags()->with('tagKey')->get()->mapWithKeys(function ($tag) {
            return [$tag->tagKey->key => $tag->value];
        })->sortKeys();

        if (! $key) {
            return $tags->all();
        }

        return $tags->only(is_array($key) ? $key : [$key])->all();
    }

    /**
     * Set one or more tags for this model.
     *
     * @param  array|null  $kvp
     */
    public function setTag($kvp)
    {
        foreach ($kvp as $key => $value) {
            $tagKey = TagKey::firstOrCreate(['key' => $key]);
            $this->tags()->updateOrCreate(
                [
                    'tag_key_id' => $tagKey->tag_key_id,
                    'object_type' => static::class,
                    'object_id' => $this->getKey(),
                ],
                ['value' => $value]
            );
        }

        return $this->getTag(array_keys($kvp));
    }

    /**
     * Delete tag or tags for this model by key
     *
     * @param  array|string  $key
     */
    public function deleteTag($key)
    {
        $tags = $this->tags()->with('tagKey')->get();

        $deleted = [];
        foreach ((is_array($key) ? $key : [$key]) as $k) {
            $tag = $tags->firstWhere('tagKey.key', $k);
            if ($tag) {
                $deleted += [$tag->tagKey->key => null];
                $tag->delete();
            }
        }

        return $deleted;
    }
}

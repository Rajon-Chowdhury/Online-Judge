<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'slug', 'problem_description', 'input_description', 'output_description', 'constraint_description', 'notes', 'time_limit', 'memory_limit', 'checker',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($problem) {
            // produce a slug based on the activity title
            $slug = \Str::slug($problem->name);

            // check to see if any other slugs exist that are the same & count them
            $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            while(1){
                $tmpSlug = $count ? "{$slug}-{$count}" : $slug;
                if(static::where('slug', '=', $tmpSlug)->exists()){
                    $count ++;
                    continue;
                }
                break;
            }
            
            // if other slugs exist that are the same, append the count to the slug
            $slug = $count ? "{$slug}-{$count}" : $slug;

            $problem->slug = $slug;

        });
        // auto-sets values on creation
        static::created(function ($problem) {
            $problem->moderator()->attach(auth()->user()->id, [
                'role'        => 'owner',
                'is_accepted' => '1',
            ]);
        });
    }

    public function testCases()
    {
        return $this->hasMany(ProblemTestCase::class);
    }
    public function moderator()
    {
        return $this->belongsToMany(User::class, 'problem_moderator', 'problem_id', 'user_id')->withPivot(['role', 'is_accepted']);
    }
}
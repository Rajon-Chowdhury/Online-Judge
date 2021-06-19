<?php

namespace App\Models;

use App\Models\Checker;
use App\Models\Traits\Problem\HasLanguage;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasLanguage;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'slug', 'problem_description', 'input_description', 'output_description', 'constraint_description', 'notes', 'time_limit', 'memory_limit', 'checker_type', 'default_checker', 'custom_checker','language_auto_update'
    ];

    protected $appends = ['authUserRole'];

    protected $casts = [
        'language_auto_update' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($problem) {
            // produce a slug based on the activity title
            $slug = \Str::slug($problem->name);

            // check to see if any other slugs exist that are the same & count them
            $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            while (1) {
                $tmpSlug = $count ? "{$slug}-{$count}" : $slug;
                if (static::where('slug', '=', $tmpSlug)->exists()) {
                    $count++;
                    continue;
                }
                break;
            }

            // if other slugs exist that are the same, append the count to the slug
            $slug = $count ? "{$slug}-{$count}" : $slug;

            $problem->slug            = $slug;
            $problem->default_checker = "lcmp";
        });
        // auto-sets values on creation
        static::created(function ($problem) {
            $problem->moderator()->attach(auth()->user()->id, [
                'role'        => 'owner',
                'is_accepted' => '1',
            ]);
        });

        static::deleting(function ($problem) {
            $testCases = $problem->testCases()->get();
            foreach ($testCases as $key => $testCase) {
                $testCase->delete();
            }
        });
    }

    public function testCases()
    {
        return $this->hasMany(ProblemTestCase::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class)->withPivot('time_limit', 'memory_limit');
    }

    public function languageList()
    {
        $problemLanguages = $this->languages()->get();

        if ($this->language_auto_update) {
            $languages        = Language::where(['is_archive' => 0])->get();
            $problemLanguages = $languages->merge($problemLanguages);
        }

        $problemLanguages = $problemLanguages->filter(function ($item) {
            $item->time_limit   = !empty($item->pivot) ? $item->pivot->time_limit : 1.0;
            $item->memory_limit = !empty($item->pivot) ? $item->pivot->memory_limit : 1.0;
            $item->is_checked   = 1;

            return $item;
        });

        return $problemLanguages;
    }

    public function testCasesSample()
    {
        return $this->testCases()->where(['sample' => 1]);
    }

    public function owner()
    {
        return $this->moderator()->firstOrFail();
    }

    public function moderator()
    {
        return $this->belongsToMany(User::class, 'problem_moderator', 'problem_id', 'user_id')->withPivot(['role', 'is_accepted'])->withTimestamps();
    }

    public function judgeProblem()
    {
        return $this->hasOne(JudgeProblem::class);
    }

    public function defaultChecker()
    {
        $checker = Checker::where(['name' => $this->default_checker])->first();
        return !$checker ? Checker::first() : $checker;
    }

    public function getAuthUserRoleAttribute()
    {
        return $this->moderator()->where('user_id', auth()->user()->id)->firstOrFail()->pivot->role;
    }

    public function userRole($userId)
    {
        return $this->moderator()->where('user_id', $userId)->firstOrFail()->pivot->role;
    }
}

<?php

namespace App\Policies;

use App\Models\ExamPaper;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPaperPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('read exam paper');
    }

    public function view(User $user, ExamPaper $examPaper): bool
    {
        return $user->can('read exam paper')
            && $examPaper->exam?->semester?->school_id === $user->school_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create exam paper');
    }

    public function update(User $user, ExamPaper $examPaper): bool
    {
        return $user->can('update exam paper')
            && $examPaper->exam?->semester?->school_id === $user->school_id;
    }

    public function delete(User $user, ExamPaper $examPaper): bool
    {
        return $user->can('delete exam paper')
            && $examPaper->exam?->semester?->school_id === $user->school_id;
    }

    public function publish(User $user, ExamPaper $examPaper): bool
    {
        return $user->can('publish exam paper')
            && $examPaper->exam?->semester?->school_id === $user->school_id;
    }

    public function seal(User $user, ExamPaper $examPaper): bool
    {
        return $user->can('seal exam paper')
            && $examPaper->exam?->semester?->school_id === $user->school_id;
    }
}

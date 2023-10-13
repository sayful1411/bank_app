<?php 

namespace App\Traits;

trait AvatarGeneratorTrait {
    public function generateAvatar($name){
        $avatarName = '';
        $words = explode(' ', $name);
        foreach ($words as $word) {
            $avatarName .= strtoupper(substr($word, 0, 1));
        }
        return $avatarName;
    }
}
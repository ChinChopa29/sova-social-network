<?php

namespace App\Services;

use App\Http\Requests\Profile\UpdateRequest;

class ProfileService
{
   public function getUpdateData(UpdateRequest $request): array
   {
      $fields = [
         'name',
         'gender',
         'birthday',
         'bio',
         'phone',
         'address',
         'city',
         'country',
         'website',
         'language',
         'is_public',
         'is_verified',
      ];

      $data = $request->only($fields);

      if ($request->hasFile('avatar')) {
         $avatarPath = $request->file('avatar')->store(
               'avatars/' . now()->format('Y/m/d/H_i_s'),
               'public'
         );
         $data['avatar'] = '/storage/' . $avatarPath;
      }

      if ($request->hasFile('background_image')) {
         $coverPath = $request->file('background_image')->store(
               'covers/' . now()->format('Y/m/d/H_i_s'),
               'public'
         );
         $data['background_image'] = '/storage/' . $coverPath;
      }

      return $data;
   }
}
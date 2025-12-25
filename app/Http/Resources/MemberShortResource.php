<?php

namespace App\Http\Resources;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Member */
class MemberShortResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'                      => $this->id,
            //            'father_id'                  => $this->father_id,
            //            'mother_id'                  => $this->mother_id,
            'head_of_the_family_id'   => $this->head_of_the_family_id,
            'head_of_the_family_name' => data_get($this->whenLoaded('headOfTheFamily'), 'name'),
            //            'relation_id'                => $this->relation_id,
            'name'                    => $this->name,
            'name_en'                 => $this->name_en,
            'gender'                  => $this->gender,
            'birth_date'              => $this->birth_date,
            'expire_date'             => $this->expire_date,
            'phone'                   => $this->phone,
            //            'phone_verified_at'          => $this->phone_verified_at,
            //            'email'                      => $this->email,
            //            'email_verified_at'          => $this->email_verified_at,
            //            'device_token'               => $this->device_token,
            //            'device_serial'              => $this->device_serial,
            //            'blood_group'                => $this->blood_group,
            //            'address'                    => $this->address,
            //            'occupation'                 => $this->occupation,
            //            'qualification'              => $this->qualification,
            'avatar'                  => $this->avatar,
            //            'slider'                     => $this->slider,
            'status'                  => $this->status,
            'reason'                  => $this->reason,
            //            'notification_status'        => $this->notification_status,
            //            'is_private'                 => $this->is_private,
            //            'relationShip_status'        => $this->relationShip_status,
            //            'profession'                 => $this->profession,
            //            'profession_type'            => $this->profession_type,
            //            'work_address'               => $this->work_address,
            //            'mosal'                      => $this->mosal,
            //            'is_login_auth'              => $this->is_login_auth,
            //            'mother_name'                => $this->mother_name,
            //            'father_name'                => $this->father_name,
            //            'education'                  => $this->education,
            //            'pancard'                    => $this->pancard,
            'unique_number'           => $this->unique_number,
            //            'created_at'                 => $this->created_at,
            //            'updated_at'                 => $this->updated_at,
            'total_donation'          => $this->total_donation,
            //            'announcements_count'        => $this->announcements_count,
            //            'clients_count'              => $this->clients_count,
            //            'donations_count'            => $this->donations_count,
            //            'game_results_count'         => $this->game_results_count,
            //            'member_galleries_count'     => $this->member_galleries_count,
            //            'notifications_count'        => $this->notifications_count,
            //            'permissions_count'          => $this->permissions_count,
            //            'read_notifications_count'   => $this->read_notifications_count,
            //            'roles_count'                => $this->roles_count,
            //            'tokens_count'               => $this->tokens_count,
            //            'unread_notifications_count' => $this->unread_notifications_count,

            //            'kuldevi_id'      => $this->kuldevi_id,
            'native_place_id'         => $this->native_place_id,
            //            'zone_id'         => $this->zone_id,

            //            'donations'    => DonationResource::collection($this->whenLoaded('donations')),
            //            'game_results' => GameResultResource::collection($this->whenLoaded('gameResults')),
            //            'kuldevi'      => KuldeviResource::make($this->whenLoaded('kuldevi')),
            'native_place'            => NativePlaceResource::make($this->whenLoaded('nativePlace')),
            //            'zone'         => ZoneResource::make($this->whenLoaded('zone')),
        ];
    }
}

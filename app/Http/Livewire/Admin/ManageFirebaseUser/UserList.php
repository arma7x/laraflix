<?php

namespace App\Http\Livewire\Admin\ManageFirebaseUser;

use App\Facades\Helpers\FirebaseHelper as Firebase;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserList extends Component
{
    use AuthorizesRequests;

    public $user;
    public $search;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->searchUser();
    }

    public function searchUser()
    {
        try {
            if ($this->search == null || trim($this->search) == '') {
                $this->user = null;
            } else if (trim($this->search)[0] == '+') {
                $this->user = Firebase::auth()->getUserByPhoneNumber(trim($this->search))->jsonSerialize();
            } else if (Validator::make(['email' => trim($this->search)], ['email' => 'required|email'])->fails() === false) {
                $this->user = Firebase::auth()->getUserByEmail(trim($this->search))->jsonSerialize();
            } else {
                $this->user = Firebase::auth()->getUser(trim($this->search))->jsonSerialize();
            }
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            $this->user = $e->getMessage();
        } catch(\Exception $e) {
            $this->user = null;
        }
    }

    // TODO
    private function getCustomUserClaims($uid)
    {
        $this->authorize('manage-user');
        return Firebase::auth()->getUser($uid)->customClaims;
    }

    // TODO
    public function setCustomUserClaims($uid, $claims = [])
    {
        $this->authorize('manage-user');
    }

    public function disableUser($uid)
    {
        $this->authorize('manage-user');
        Firebase::auth()->disableUser($uid);
    }

    public function enableUser($uid)
    {
        $this->authorize('manage-user');('manage-user');
        Firebase::auth()->enableUser($uid);
    }

    public function revokeRefreshTokens($uid)
    {
        $this->authorize('manage-user');
        Firebase::auth()->revokeRefreshTokens($uid);
    }

    public function deleteUser($uid)
    {
        $this->authorize('manage-user');
        try {
            Firebase::auth()->deleteUser($uid);
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            $this->user = $e->getMessage();
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            $this->user = $e->getMessage();
        }
    }

    public function render()
    {
        $this->searchUser();
        return view('livewire.admin.manage-firebase-user.user-list');
    }
}

<?php


namespace App\Traits;


use App\Jobs\BitmessageNotify;
use App\Notification;

trait Notifiable
{

    /**
     * Create new notifications for specified user
     *
     * @param string $content
     */
    public function notify(string $content,string $routeName = null,string $routePramas = null) {
        $this->notifications()->create(['description' => $content,'route_name'=>$routeName,'route_params'=> $routePramas]);

        /**
         * Bitmessage
         */
        if (config('bitmessage.enabled')){
            // if its enabled send message
            BitmessageNotify::dispatch('Notification from marketplace',$content,'BM-2cUZmgDqjBeFCEfnj6DsgZs7LAYoEasFt9')->delay(now()->addSecond(1));;
        }
    }

    /**
     * Return user's unread notifications
     *
     * @return mixed
     */
    public function unreadNotifications(){
        return $this->notifications()->where('read',0);
    }
}
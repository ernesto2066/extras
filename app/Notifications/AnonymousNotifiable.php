<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class AnonymousNotifiable
{
    use Notifiable;

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * Añade una ruta de entrega.
     *
     * @param string $channel
     * @param mixed $route
     * @return $this
     */
    public function route($channel, $route)
    {
        $this->routes[$channel] = $route;

        return $this;
    }

    /**
     * Obtiene las rutas de entrega para el canal dado.
     *
     * @param string $channel
     * @return mixed
     */
    public function routeNotificationFor($channel)
    {
        return $this->routes[$channel] ?? null;
    }
}

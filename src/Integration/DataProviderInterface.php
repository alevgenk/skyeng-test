<?php


namespace src\Integration;


interface DataProviderInterface
{
    public function get(array $request);
}

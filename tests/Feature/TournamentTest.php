<?php

it('has tournament page', function () {
    $response = $this->get('/tournament');

    $response->assertStatus(200);
});

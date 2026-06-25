<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.activity-logs', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.pemilih', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.accounts', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('admin.team', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('kecamatan.pemilih.{kecamatanId}', function ($user, $kecamatanId) {
    return $user->role === 'kecamatan' && $user->kecamatan_id === $kecamatanId;
});

Broadcast::channel('kecamatan.team.{kecamatanId}', function ($user, $kecamatanId) {
    return $user->role === 'kecamatan' && $user->kecamatan_id === $kecamatanId;
});

Broadcast::channel('desa.pemilih.{desaId}', function ($user, $desaId) {
    return $user->role === 'desa' && $user->desa_id === $desaId;
});

Broadcast::channel('desa.team.{desaId}', function ($user, $desaId) {
    return $user->role === 'desa' && $user->desa_id === $desaId;
});

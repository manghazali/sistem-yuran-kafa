@extends('errors::minimal')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('message', $exception->getMessage() ?: 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.')

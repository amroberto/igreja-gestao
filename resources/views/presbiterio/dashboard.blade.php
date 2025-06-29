
@extends('adminlte::page')

   @section('title', content: 'Dashboard - Presbitério')

   @section('content_header')
       <h1>Dashboard - Presbitério</h1>
   @stop

   @section('content')
       <div class="container-fluid">
           <x-adminlte-card title="Bem-vindo(a) ao Dashboard Presbitério" theme="secondary">
               <p>Este é o painel para usuários do Presbitério da <b><i>{{ $community->name }}.</b></i></p>
           </x-adminlte-card>
       </div>
   @stop

   @section('js')
       <script>
           console.log('Dashboard Presbitério carregado');
       </script>
   @stop
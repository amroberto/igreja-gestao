
@extends('adminlte::page')

   @section('title', content: 'Dashboard - Admin')

   @section('content_header')
       <h1>Dashboard - Admin</h1>
   @stop

   @section('content')
       <div class="container-fluid">
           <x-adminlte-card title="Bem-vindo(a) ao Dashboard Admin" theme="secondary">
               <p>Este é o painel para usuários Administradores.</p>
           </x-adminlte-card>
       </div>
   @stop

   @section('js')
       <script>
           console.log('Dashboard Admin carregado');
       </script>
   @stop
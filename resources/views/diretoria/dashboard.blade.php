@extends('adminlte::page')

   @section('title', content: 'Dashboard - Diretoria')

   @section('content_header')
       <h1>Dashboard da Diretoria</h1>
   @stop

   @section('content')
       <div class="container-fluid">
           <x-adminlte-card title="Bem-vindo(a) ao Dashboard da Diretoria" theme="secondary">
               <p>Este é o painel para usuários da Diretoria.</p>
           </x-adminlte-card>
       </div>
   @stop

   @section('js')
       <script>
           console.log('Dashboard da Diretoria carregado');
       </script>
   @stop

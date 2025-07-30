<select x-model="domicilio.tipoVivienda" class="…">
  <option value="">--Seleccione--</option>
  @foreach($tiposVivienda as $t)
    <option>{{ $t }}</option>
  @endforeach
</select>

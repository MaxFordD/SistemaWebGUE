@if($stats)
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="bi bi-newspaper fs-1"></i>
                </div>
                <h3 class="h4 fw-bold mb-1">{{ $stats->total_noticias ?? 0 }}</h3>
                <p class="text-muted mb-0">Total Noticias</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-file-earmark-text fs-1"></i>
                </div>
                <h3 class="h4 fw-bold mb-1">{{ $stats->total_mesa_partes ?? 0 }}</h3>
                <p class="text-muted mb-0">Documentos Recibidos</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-clock-history fs-1"></i>
                </div>
                <h3 class="h4 fw-bold mb-1">{{ $stats->mesa_pendientes ?? 0 }}</h3>
                <p class="text-muted mb-0">Pendientes</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="bi bi-people fs-1"></i>
                </div>
                <h3 class="h4 fw-bold mb-1">{{ $stats->total_usuarios ?? 0 }}</h3>
                <p class="text-muted mb-0">Usuarios Registrados</p>
            </div>
        </div>
    </div>
</div>
@endif

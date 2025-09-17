<div 
    x-data="{
        selectedProducts: @js($getState() ?? []),
        products: @js($getProducts()),
        search: '',
        
        get filteredProducts() {
            if (!this.search) return this.products;
            return this.products.filter(product => 
                product.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        toggleProduct(productId) {
            if (this.selectedProducts.includes(productId)) {
                this.selectedProducts = this.selectedProducts.filter(id => id !== productId);
            } else {
                this.selectedProducts.push(productId);
            }
            $wire.set('{{ $getStatePath() }}', this.selectedProducts);
        }
    }"
    style="padding: 1rem;"
>
    <input 
        type="text" 
        x-model="search" 
        placeholder="Buscar productos..."
        style="width: 100%; margin-bottom: 1rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: black;"
    >

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem;">
        <template x-for="product in filteredProducts" :key="product.id">
            <div
                @click="toggleProduct(product.id)"
                :style="{
                    border: selectedProducts.includes(product.id) ? '2px solid #3b82f6' : '1px solid #ddd',
                    cursor: 'pointer'
                }"
                style="padding: 0.5rem; border-radius: 4px; transition: all 0.2s;"
            >
                <!-- imagen -->
                <div style="height: 120px; overflow: hidden; margin-bottom: 0.5rem;">
                    <img 
                        x-show="product.image_url" 
                        :src="product.image_url" 
                        :alt="product.name"
                        style="width: 100%; height: 100%; object-fit: cover;"
                    >
                    <div 
                        x-show="!product.image_url" 
                        style="width: 100%; height: 100%; background: #f3f4f6; display: flex; align-items: center; justify-content: center;"
                    >
                        <span style="color: #9ca3af;">Sin imagen</span>
                    </div>
                </div>
                <!-- info -->
                <div style="font-weight: 500;" x-text="product.name"></div>
                <div style="font-weight: 500;" x-text="product.price"></div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                    <span style="font-size: 0.75rem; display:flex; justify-content: space-between;" :class="product.stock_status_class">
                        <span x-text="product.stock_status === 'in_stock' ? 'Disponible' 
                            : product.stock_status === 'low_stock' ? 'Bajo stock' 
                            : 'Agotado'">
                        </span>
                    </span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                    <span style="font-size: 0.75rem;">Inventario total</span>
                    <span style="font-size: 0.75rem;" x-text="product.total_stock"></span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                    <span style="font-size: 0.75rem;">Variantes</span>
                    <span style="font-size: 0.75rem;">
                        <span x-show="product.has_variants">
                            <span x-text="product.variants_count"></span>
                        </span>
                    </span>
                </div>

                <div style="font-size: 0.75rem; color: #6b7280;">
                    <span x-show="selectedProducts.includes(product.id)" style="color: #3b82f6;">✓ Seleccionado</span>
                </div>
            </div>
        </template>
    </div>

    <input 
        type="hidden" 
        name="{{ $getName() }}"
        x-model="JSON.stringify(selectedProducts)"
        x-ref="hiddenInput"
    >
</div>
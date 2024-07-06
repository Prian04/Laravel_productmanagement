@extends('layout.main')
@section('content')
    <div class="container mt-5">
        <h2>Product Management</h2>
        <button class="btn btn-success mb-3" id="create-new-product">Add Product</button>
        <table class="table table-bordered" id="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Images</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modal for Add Product -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="product_id">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_price" class="form-label">Product Price</label>
                            <input type="number" step="0.01" class="form-control" id="product_price" name="product_price" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_description" class="form-label">Product Description</label>
                            <textarea class="form-control" id="product_description" name="product_description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="product_images" class="form-label">Product Images</label>
                            <input type="file" class="form-control" id="product_images" name="product_images[]" multiple required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Edit Product -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_product_id" name="id">
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_price" class="form-label">Product Price</label>
                            <input type="number" step="0.01" class="form-control" id="edit_product_price" name="product_price" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_description" class="form-label">Product Description</label>
                            <textarea class="form-control" id="edit_product_description" name="product_description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_images" class="form-label">Product Images</label>
                            <input type="file" class="form-control" id="edit_product_images" name="product_images[]" multiple>
                        </div>
                        <button type="submit" class="btn btn-primary" id="editSaveBtn">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="footer">
    <hr>
</div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
           // $('#products-table').DataTable();
            
           var table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('products.list') !!}',
                columns: [
                    { data: 'id' },
                    { data: 'product_name' },
                    { data: 'product_description' },
                    { data: 'product_price' },
                    { data: 'product_images', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                info: false,
                paging: false,
            });
            // add modal
            $('#create-new-product').click(function() {
                $('#saveBtn').val("create-product");
                $('#product_id').val('');
                $('#productForm').trigger("reset");
                $('#productModalLabel').text("Add Product");
                $('#productModal').modal('show');
            });

            // edit modal
            $('body').on('click', '.edit-product', function () {
            var product_id = $(this).data('id');
            $.get("{{ route('products_edit', ['id' => ':id']) }}".replace(':id', product_id), function (data) {
                $('#edit_product_id').val(data.id);
                    $('#edit_product_name').val(data.product_name);
                    $('#edit_product_price').val(data.product_price);
                    $('#edit_product_description').val(data.product_description);
                    $('#editProductModal').modal('show');

                // Handle images display in edit modal
                var imagesHtml = '';
                $.each(JSON.parse(data.product_images), function(index, image) {
                    imagesHtml += '<img src="' + image + '" width="50" class="img-thumbnail mr-1 mb-1">';
                });
                $('#product_images_preview').html(imagesHtml);
            });
        });


            // add submit from modal
            $('#productForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ route('products_store') }}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $('#productForm').trigger("reset");
                        $('#productModal').modal('hide');
                         table.ajax.reload();
                       //location.reload();
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });

            // update submit from edit modal
            $('#editProductForm').submit(function(e) {
                e.preventDefault();
                var product_id = $('#edit_product_id').val();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ route('products_update', ':id') }}".replace(':id', product_id),
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        //alert("Product updated successfully");
                        $('#editProductForm').trigger("reset");
                        $('#editProductModal').modal('hide');
                        table.ajax.reload();
                        //location.reload();
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });


             $('body').on('click', '.delete-product', function () {
            var product_id = $(this).data("id");
            if(confirm("Are you sure you want to delete this product?")) {
                $.ajax({
                    type: "DELETE",
                    headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
                    url: "{{ route('products_delete', ':id') }}".replace(':id', product_id),
                    success: function (response) {
                        // Remove the deleted product row from the table
                        $("#product-" + product_id).remove();
                        // Show success message or handle accordingly
                        table.ajax.reload();
                        alert(response.success);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                        // Handle error scenario if needed
                    }
                });
            }
            });


        });
    </script>

@endsection
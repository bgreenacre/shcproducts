<ul>
<?php foreach ($products as $product): ?>
<li class="shcp_product" data-post_id="<?php echo $product->ID; ?>">
<img src="<?php echo $product->product_image; ?>" alt="<?php echo __('Product Image'); ?>" />
<p><b><?php echo $product->post_title; ?>:</b> <?php echo $product->Price; ?></p>
</li>
<?php endforeach; ?>
<li class="shcp_product" data-post_id="222">
<img src="http://0.gravatar.com/avatar/878bb54960e64fc82c0ed586fa95f8c2?s=32&d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&r=G" alt="product image" height="60px" width="60px" />
<p><b>Product Title 1</b></p>
<p>$30.00</p>
<p>Stuff</p>
<p>More stuff.</p>
</li>
<li class="shcp_product" data-post_id="222">
<img src="http://0.gravatar.com/avatar/878bb54960e64fc82c0ed586fa95f8c2?s=32&d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&r=G" alt="product image" height="60px" width="60px" />
<p><b>Product Title 1</b></p>
<p>$30.00</p>
<p>Stuff</p>
<p>More stuff.</p>
</li>
<li class="shcp_product" data-post_id="222">
<img src="http://0.gravatar.com/avatar/878bb54960e64fc82c0ed586fa95f8c2?s=32&d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&r=G" alt="product image" height="60px" width="60px" />
<p><b>Product Title 1</b></p>
<p>$30.00</p>
<p>Stuff</p>
<p>More stuff.</p>
</li>
<li class="shcp_product" data-post_id="222">
<img src="http://0.gravatar.com/avatar/878bb54960e64fc82c0ed586fa95f8c2?s=32&d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&r=G" alt="product image" height="60px" width="60px" />
<p><b>Product Title 1</b></p>
<p>$30.00</p>
<p>Stuff</p>
<p>More stuff.</p>
</li>
</ul>

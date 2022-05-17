<?php 
global $wpdb; 
$currID = get_current_user_id(  );

?>
<div id="wc-rabbit-additional-address">
    <div id="wr-additional-billing-address">
        <h3 class="wr-woottl">Additional billing addresses</h3>

        <div class="wr-addresses-cards">
            <?php
            $billing_address = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = $currID AND `type` = 'billing' AND address_id > 0");
            
            if($billing_address){
                foreach($billing_address as $billing){
                    ?>
                    <div class="wr-address">
                        <?php
                        if(get_user_meta($currID, 'is_wr_default_billing', true) === $billing->ID){
                            ?>
                            <span title="As default selected" class="selected_address">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 1000 1000" fill="#00bfff" width="20px" height="20px" enable-background="new 0 0 1000 1000" xml:space="preserve">
                                <metadata> Svg Vector Icons : http://www.onlinewebfonts.com/icon </metadata>
                                <g><path d="M500,990C229.4,990,10,770.6,10,500C10,229.4,229.4,10,500,10c270.6,0,490,219.4,490,490C990,770.6,770.7,990,500,990L500,990z M754.9,327.7l-15.8-15.8c-8.7-8.7-22.8-8.7-31.5,0L381,631.8l-120-125.5c-3.9-3.9-14.1,0-22.9,8.6l-15.7,15.7c-8.7,8.7-12.6,19-8.7,22.9l160,167.5c3.9,3.9,14.2,0.1,22.9-8.7l15.7-15.7c2.8-2.8,4.8-5.6,6.5-8.4l335.9-329C763.5,350.5,763.5,336.4,754.9,327.7L754.9,327.7z"/></g>
                                </svg>
                            </span>
                            <?php
                        }
                        ?>
                        <div class="wr-add-contents">
                            <ul>
                                <li><?php echo $billing->country ?></li>
                                <li><?php echo $billing->city ?></li>
                                <li><?php echo $billing->address ?></li>
                            </ul>
                            
                            <div class="wr-address-buttons">
                                <?php
                                    if(get_user_meta($currID, 'is_wr_default_billing', true) === $billing->ID){
                                        echo '<button data-id="'.$billing->ID.'" class="wr-unset_default-billing">Unset default</button>';
                                    }else{
                                        echo '<button data-id="'.$billing->ID.'" class="wr-set_default-billing">Set as default</button>';
                                    }
                                ?>
                                
                                <button data-id="<?php echo $billing->ID ?>" class="wr-delete-billing">Delete</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }else{
                echo "<div class='wr-error'>There is no additional address.</div>";
            }
            ?>
        </div>
    </div>
    <div id="additional-shipping-address">
        <h3 class="woottl">Additional shipping addresses</h3>

        <div class="wr-addresses-cards">
            <?php
            $shipping_address = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wcrabbit_customers WHERE user_id = $currID AND `type` = 'shipping' AND address_id > 0");
            
            if($shipping_address){
                foreach($shipping_address as $shipping){
                    ?>
                    <div class="wr-address">
                        <?php
                        if(get_user_meta($currID, 'is_wr_default_shipping', true) === $shipping->ID){
                            ?>
                            <span title="As default selected" class="selected_address">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 1000 1000" fill="#00bfff" width="20px" height="20px" enable-background="new 0 0 1000 1000" xml:space="preserve">
                                <metadata> Svg Vector Icons : http://www.onlinewebfonts.com/icon </metadata>
                                <g><path d="M500,990C229.4,990,10,770.6,10,500C10,229.4,229.4,10,500,10c270.6,0,490,219.4,490,490C990,770.6,770.7,990,500,990L500,990z M754.9,327.7l-15.8-15.8c-8.7-8.7-22.8-8.7-31.5,0L381,631.8l-120-125.5c-3.9-3.9-14.1,0-22.9,8.6l-15.7,15.7c-8.7,8.7-12.6,19-8.7,22.9l160,167.5c3.9,3.9,14.2,0.1,22.9-8.7l15.7-15.7c2.8-2.8,4.8-5.6,6.5-8.4l335.9-329C763.5,350.5,763.5,336.4,754.9,327.7L754.9,327.7z"/></g>
                                </svg>
                            </span>
                            <?php
                        }
                        ?>

                        <div class="wr-add-contents">
                            <ul>
                                <li><?php echo $shipping->country ?></li>
                                <li><?php echo $shipping->city ?></li>
                                <li><?php echo $shipping->address ?></li>
                            </ul>
                            
                            <div class="wr-address-buttons">
                                <?php
                                if(get_user_meta($currID, 'is_wr_default_shipping', true) === $shipping->ID){
                                    echo '<button data-id="'.$shipping->ID.'" class="wr-unset_default-shipping">Unset default</button>';
                                }else{
                                    echo '<button data-id="'.$shipping->ID.'" class="wr-set_default-shipping">Set as default</button>';
                                }
                                ?>
                                <button data-id="<?php echo $shipping->ID ?>" class="wr-delete-shipping">Delete</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }else{
                echo "<div class='wr-error'>There is no additional address.</div>";
            }
            ?>
        </div>
    </div>
</div>
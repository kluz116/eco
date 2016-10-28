<div class="example-modal">
            <div id="payments" class="modal modal-default  fade" style="display: none;">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-shopping-cart"></i> Make Cash Payment</h4>
                  </div>
                  <div class="modal-body" id="content">
                  <div class="box-body">
                   <div id="response"></div>
                      <div class="form-group">
                    
           
                    <?php
                   $api->GetAllClientsPayments();
                   ?>
                     
                    </div>
              <div class="form-group">
                <label>Payment Date:</label>

                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input type="text" class="form-control pull-right" id="datepicker">
                </div>
                <!-- /.input group -->
              </div>
                  
                <div class="form-group">
                        <label>Amount</label>
                          <input type="text" class="form-control" id="amount" placeholder="Put Amount Paid">
                          
                </div>
                <div class="form-group">
                    <label>Payment Mode</label>
                    <input type="text" class="form-control" id="mode" value="cash" readonly>
                          
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <textarea class="form-control" id="remarks" cols="2px" rows="2px"></textarea>
                          
                </div>
                    <div class="form-group">
                      <div class="col-md-4"></div>
                      <div class="col-md-4">
                        <button class="btn btn-primary" id="addPayment" type="submit"><i class="fa fa-plus"></i><i class="fa fa-cash"></i> Make Cash Payment</button>
                      </div>
                      <div class="col-md-4"></div>
                          
                        </div>
                    </div>
                    
                  </div><!-- /.box-body -->
               
                  </div>
                 
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          <!-- /.example-modal -->
  
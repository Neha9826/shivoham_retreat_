			<div class="uk-container">
				<div data-uk-grid class="impx-negative-top uk-margin-medium-bottom slide-form">
					<div class="uk-flex uk-flex-center" data-uk-grid>
						<div class="uk-width-1-1">
							<div class="impx-hp-booking-form hp2">
								<h6 class="uk-heading-line uk-text-center impx-text-white uk-margin-bottom uk-text-uppercase"><span>Booking Form</span></h6>
								<form class="uk-grid-margin-small uk-child-width-1-6@xl uk-child-width-1-6@l uk-child-width-1-6@m uk-child-width-1-3@s" data-uk-grid>
									
								    <div class="uk-form-controls">
									    <div class="uk-inline">
									    	<label class="uk-form-label">Check-in Date</label>
									    	<span class="uk-form-icon" data-uk-icon="icon: calendar"></span>
									        <input class="uk-input booking-arrival uk-border-rounded" type="date" name="check_in" id="check_in"
                               value="<?= htmlspecialchars($check_in) ?>" required placeholder="m/dd/yyyy">
									    </div>
									</div>
								    <div class="uk-form-controls">
									    <div class="uk-inline">
									    	<label class="uk-form-label">Check-out Date</label>
									    	<span class="uk-form-icon" data-uk-icon="icon: calendar"></span>
									        <input class="uk-input booking-departure uk-border-rounded" type="date" name="check_out" id="check_out"
                               value="<?= htmlspecialchars($check_out) ?>" required placeholder="m/dd/yyyy">
									    </div>
								    </div>
								    <div class="uk-form-controls uk-position-relative">
								    	<label class="uk-form-label" for="form-guest-select">No. of Adults</label>
								    	<span class="uk-form-icon select-icon" data-uk-icon="icon: users"></span>
							            <input type="number" name="guests" min="1"
                               value="<?= htmlspecialchars($guests) ?>" class="uk-input booking-departure uk-border-rounded" required>
								    </div>
									<div class="uk-form-controls uk-position-relative">
								    	<label class="uk-form-label" for="form-guest-select">No. of Children</label>
								    	<span class="uk-form-icon select-icon" data-uk-icon="icon: users"></span>
							            <input type="number" name="children" min="0"
                               value="<?= htmlspecialchars($children) ?>" class="uk-input booking-departure uk-border-rounded" >
								    </div>
								   <div class="uk-form-controls uk-position-relative">
								    	<label class="uk-form-label" for="form-rooms-select">Rooms</label>
								    	<span class="uk-form-icon select-icon" data-uk-icon="icon: album"></span>
							            <input type="number" name="no_of_rooms" min="1"
                               value="<?= htmlspecialchars($no_of_rooms) ?>" class="uk-input booking-departure uk-border-rounded" required>
								    </div>
								    <div>
								    	<label class="uk-form-label empty-label">&nbsp;</label>
								        <button class="uk-button uk-width-1-1"type="submit">Check</button>
								    </div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		
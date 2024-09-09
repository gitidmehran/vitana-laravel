<div class="row setup-content" id="step-{{$stepNo ?? '1'}}" data-type="nutrition">
    <h3> Nutrition</h3>
    <div class="form-group mb-3">
        <label class="control-label">In the past 7 days, how many servings of fruits and vegetables have you eaten each day?</label>
    </br>
    <span class="text-secondary">(1 serving = 1 cup of fresh vegetables, ½ cup of cooked vegetables, or 1 medium piece of fruit. 1 cup = size of a baseball.)</span>
    <input 
    maxlength="100" 
    type="number" 
    min="0"  
    name="nutrition[fruits_vegs]" 
    class="form-control tab3 mt-2" 
    placeholder="servings per day" 
    value="{{$row['fruits_vegs'] ?? ''}}"
    />
</div>
<div class="form-group mb-3">
    <label class="control-label">In the past 7 days, how many servings of high fiber or whole (not refined) grain foods have you typically eaten each day?</label>
</br><span class="text-secondary">(1 serving = 1 slice of 100% whole wheat bread, 1 cup of whole-grain or high-fiber ready-to-eat cereal, ½ cup of cooked cereal such as oatmeal, or ½ cup of cooked brown rice or whole wheat pasta.)</span>
<input 
maxlength="100" 
type="number" 
min="0"  
name="nutrition[whole_grain_food]" 
class="form-control tab3 mt-2" 
placeholder="servings per day"
value="{{$row['whole_grain_food'] ?? ''}}"
/>
</div>
<div class="form-group mb-3">
    <label class="control-label">In the past 7 days, how many servings of fried or high-fat foods have you typically eaten each day?</label>
</br><span class="text-secondary">(Examples include fried chicken, fried fish, bacon, French fries, potato chips, corn chips, doughnuts, creamy salad dressings, and foods made with whole milk, cream, cheese, or mayonnaise.)</span>
<input 
maxlength="100" 
type="number" 
min="0"  
name="nutrition[high_fat_food]" 
class="form-control tab3 mt-2" 
placeholder="servings per day"
value="{{$row['high_fat_food'] ?? ''}}"
/>
</div>
<div class="form-group mb-3">
    <label class="control-label">In the past 7 days, how many sugar-sweetened (not diet) beverages did you typically consume each day?</label>
    <input 
    maxlength="100" 
    type="number" 
    min="0"  
    name="nutrition[sugar_beverages]" 
    class="form-control tab3 mt-2" 
    placeholder="servings per day"
    value="{{$row['sugar_beverages'] ?? ''}}"
    />
</div>

<div class="pull-right align-items-end">
    <div class="btn-group flex-row-reverse" role="group" aria-label="Basic example">
      <button class="btn btn-primary nextBtn mx-2" type="button" >Next</button>
      <button class="btn btn-primary prevBtn" type="button">Previous</button>
  </div>
</div>
</div>
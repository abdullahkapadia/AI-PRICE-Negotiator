

import os
import sys
import json

class DecisionTreePredictor:
    
    
    def __init__(self, tree_dict):
        self.tree = tree_dict
    
    def predict(self, features):
        return self._traverse(features, self.tree)
    
    def _traverse(self, features, node):
        if node is None:
            return 0
        if "value" in node:
            return node["value"]
        if features[node["feature"]] <= node["threshold"]:
            return self._traverse(features, node.get("left"))
        return self._traverse(features, node.get("right"))

class RandomForestPredictor:
    
    
    def __init__(self, model_data):
        self.trees = []
        for tree_dict in model_data["model"]["trees"]:
            self.trees.append(DecisionTreePredictor(tree_dict))
    
    def predict(self, features):
        votes = {}
        for tree in self.trees:
            pred = tree.predict(features)
            votes[pred] = votes.get(pred, 0) + 1
        
        best = max(votes, key=votes.get)
        confidence = votes[best] / len(self.trees)
        return best, confidence

def rule_based_predict(features):
    
    offer_pct = features[0]
    customer_orders = features[1]
    customer_spent = features[2]
    stock = features[3]
    
    loyalty_bonus = min(customer_orders * 0.5 + customer_spent / 10000, 5)
    
    effective_threshold = 85 - loyalty_bonus
    if stock < 5:
        effective_threshold += 3
    
    if offer_pct >= 90:
        return 1, 0.90
    elif offer_pct >= effective_threshold:
        return 0, 0.60
    elif offer_pct >= 60:
        return 0, 0.40
    else:
        return -1, 0.85

def calculate_counter_price(decision, confidence, features, original_price, min_price_pct):
    
    min_price = original_price * (min_price_pct / 100)
    offer_pct = features[0]
    customer_orders = features[1]
    stock = features[3]
    days_listed = features[4]
    
    if decision == 1:
        return original_price * (offer_pct / 100)
    
    if decision == -1:
        suggested = original_price * 0.88
        if suggested < min_price:
            suggested = min_price
        return round(suggested, 2)
    
    base_discount = 0.05
    
    if customer_orders > 5:
        base_discount += 0.03
    elif customer_orders > 2:
        base_discount += 0.01
    
    if days_listed > 30:
        base_discount += 0.03
    elif days_listed > 14:
        base_discount += 0.01
    
    if stock < 5:
        base_discount -= 0.02
    
    base_discount += (confidence - 0.5) * 0.05
    
    base_discount = max(0.03, min(base_discount, 0.20))
    
    counter_price = original_price * (1 - base_discount)
    if counter_price < min_price:
        counter_price = min_price
    
    return round(counter_price, 2)

def main():
    if len(sys.argv) < 14:
        print(json.dumps({
            "success": False,
            "error": "Insufficient parameters. Need 13 feature values."
        }))
        sys.exit(1)
    
    try:
        features = [float(sys.argv[i]) for i in range(1, 14)]
    except ValueError as e:
        print(json.dumps({
            "success": False,
            "error": f"Invalid parameter: {str(e)}"
        }))
        sys.exit(1)
    
    original_price = features[11]
    min_price_pct = features[12]
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    model_path = os.path.join(script_dir, "trained_model.json")
    
    use_ml = False
    
    if os.path.exists(model_path):
        try:
            with open(model_path, 'r') as f:
                model_data = json.load(f)
            
            if model_data.get("trained", False) and model_data.get("type") == "random_forest":
                predictor = RandomForestPredictor(model_data)
                decision, confidence = predictor.predict(features)
                use_ml = True
                model_type = "ml"
        except Exception:
            pass
    
    if not use_ml:
        decision, confidence = rule_based_predict(features)
        model_type = "rule_based"
    
    offer_price = original_price * (features[0] / 100)
    counter_price = calculate_counter_price(decision, confidence, features, original_price, min_price_pct)
    
    decision_map = {1: "Accepted", 0: "Counter", -1: "Rejected"}
    decision_text = decision_map.get(decision, "Counter")
    
    if decision_text == "Counter" and counter_price <= offer_price:
        decision_text = "Accepted"
        counter_price = offer_price
        confidence = max(confidence, 0.7)
    
    if decision_text == "Accepted":
        message = f"Based on AI analysis, your offer of Rs. {offer_price:,.2f} has been accepted."
    elif decision_text == "Counter":
        message = f"AI Counter Offer: Rs. {counter_price:,.2f}. This price is optimized based on market analysis and your profile."
    else:
        suggested = original_price * 0.88
        min_price = original_price * (min_price_pct / 100)
        if suggested < min_price:
            suggested = min_price
        message = f"Your offer is too low. Try offering closer to Rs. {suggested:,.2f} for a better chance."
    
    result = {
        "success": True,
        "decision": decision_text,
        "ai_price": counter_price if decision_text != "Accepted" else offer_price,
        "confidence": round(confidence, 4),
        "model_type": model_type,
        "message": message
    }
    
    print(json.dumps(result))

if __name__ == "__main__":
    main()

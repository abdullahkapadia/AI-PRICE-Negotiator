import os
import sys
import json
import csv
import math
class DecisionNode:
    
    def __init__(self, feature_index=None, threshold=None, left=None, right=None, value=None):
        self.feature_index = feature_index
        self.threshold = threshold
        self.left = left
        self.right = right
        self.value = value

class SimpleDecisionTree:
    
    
    def __init__(self, max_depth=10, min_samples=2):
        self.max_depth = max_depth
        self.min_samples = min_samples
        self.root = None
    
    def fit(self, X, y):
        self.n_classes = len(set(y))
        self.root = self._build_tree(X, y, depth=0)
    
    def predict(self, X):
        return [self._predict_one(x, self.root) for x in X]
    
    def predict_proba(self, x):
        return self._predict_proba_one(x, self.root)
    
    def _gini(self, y):
        classes = set(y)
        n = len(y)
        if n == 0:
            return 0
        impurity = 1.0
        for c in classes:
            p = sum(1 for v in y if v == c) / n
            impurity -= p * p
        return impurity
    
    def _best_split(self, X, y):
        best_gini = float('inf')
        best_feature = None
        best_threshold = None
        n_features = len(X[0])
        
        for feature_idx in range(n_features):
            values = sorted(set(row[feature_idx] for row in X))
            for i in range(len(values) - 1):
                threshold = (values[i] + values[i+1]) / 2
                left_y = [y[j] for j in range(len(y)) if X[j][feature_idx] <= threshold]
                right_y = [y[j] for j in range(len(y)) if X[j][feature_idx] > threshold]
                
                if len(left_y) == 0 or len(right_y) == 0:
                    continue
                
                gini = (len(left_y) * self._gini(left_y) + len(right_y) * self._gini(right_y)) / len(y)
                if gini < best_gini:
                    best_gini = gini
                    best_feature = feature_idx
                    best_threshold = threshold
        
        return best_feature, best_threshold
    
    def _build_tree(self, X, y, depth):
        if depth >= self.max_depth or len(y) < self.min_samples or len(set(y)) == 1:
            counts = {}
            for v in y:
                counts[v] = counts.get(v, 0) + 1
            majority = max(counts, key=counts.get)
            return DecisionNode(value=majority)
        
        feature_idx, threshold = self._best_split(X, y)
        if feature_idx is None:
            counts = {}
            for v in y:
                counts[v] = counts.get(v, 0) + 1
            majority = max(counts, key=counts.get)
            return DecisionNode(value=majority)
        
        left_X = [X[i] for i in range(len(X)) if X[i][feature_idx] <= threshold]
        left_y = [y[i] for i in range(len(y)) if X[i][feature_idx] <= threshold]
        right_X = [X[i] for i in range(len(X)) if X[i][feature_idx] > threshold]
        right_y = [y[i] for i in range(len(y)) if X[i][feature_idx] > threshold]
        
        left_node = self._build_tree(left_X, left_y, depth + 1)
        right_node = self._build_tree(right_X, right_y, depth + 1)
        
        return DecisionNode(feature_index=feature_idx, threshold=threshold, left=left_node, right=right_node)
    
    def _predict_one(self, x, node):
        if node.value is not None:
            return node.value
        if x[node.feature_index] <= node.threshold:
            return self._predict_one(x, node.left)
        return self._predict_one(x, node.right)
    
    def _predict_proba_one(self, x, node):
        if node.value is not None:
            return node.value
        if x[node.feature_index] <= node.threshold:
            return self._predict_proba_one(x, node.left)
        return self._predict_proba_one(x, node.right)
    
    def to_dict(self):
        return self._node_to_dict(self.root)
    
    def _node_to_dict(self, node):
        if node is None:
            return None
        if node.value is not None:
            return {"value": node.value}
        return {
            "feature": node.feature_index,
            "threshold": node.threshold,
            "left": self._node_to_dict(node.left),
            "right": self._node_to_dict(node.right)
        }

class RandomForestClassifier:
    
    
    def __init__(self, n_trees=10, max_depth=8, min_samples=3):
        self.n_trees = n_trees
        self.max_depth = max_depth
        self.min_samples = min_samples
        self.trees = []
    
    def fit(self, X, y):
        import random
        self.trees = []
        n = len(X)
        
        for _ in range(self.n_trees):
            indices = [random.randint(0, n-1) for _ in range(n)]
            X_sample = [X[i] for i in indices]
            y_sample = [y[i] for i in indices]
            
            tree = SimpleDecisionTree(max_depth=self.max_depth, min_samples=self.min_samples)
            tree.fit(X_sample, y_sample)
            self.trees.append(tree)
    
    def predict(self, x_single):
        
        votes = {}
        for tree in self.trees:
            pred = tree._predict_one(x_single, tree.root)
            votes[pred] = votes.get(pred, 0) + 1
        return max(votes, key=votes.get)
    
    def predict_confidence(self, x_single):
        
        votes = {}
        for tree in self.trees:
            pred = tree._predict_one(x_single, tree.root)
            votes[pred] = votes.get(pred, 0) + 1
        
        best = max(votes, key=votes.get)
        confidence = votes[best] / len(self.trees)
        return best, confidence
    
    def to_dict(self):
        return {
            "n_trees": self.n_trees,
            "trees": [tree.to_dict() for tree in self.trees]
        }

def load_csv(filepath):
    
    X = []
    y = []
    
    with open(filepath, 'r') as f:
        reader = csv.reader(f)
        header = next(reader)
        
        for row in reader:
            if len(row) < 15:
                continue
            
            features = []
            for i in range(1, 14):
                try:
                    features.append(float(row[i]))
                except ValueError:
                    features.append(0.0)
            
            try:
                target = int(float(row[14]))
            except ValueError:
                target = 0
            
            X.append(features)
            y.append(target)
    
    return X, y, header

def train_model():
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    csv_path = os.path.join(script_dir, "training_data.csv")
    model_path = os.path.join(script_dir, "trained_model.json")
    
    if not os.path.exists(csv_path):
        print(json.dumps({
            "success": False,
            "error": "Training data not found. Please export data first."
        }))
        return
    
    X, y, header = load_csv(csv_path)
    
    if len(X) < 5:
        default_model = {
            "type": "rule_based",
            "trained": False,
            "message": "Not enough training data. Using rule-based fallback.",
            "total_samples": len(X)
        }
        with open(model_path, 'w') as f:
            json.dump(default_model, f, indent=2)
        
        print(json.dumps({
            "success": True,
            "message": "Not enough data for ML training (need at least 5 samples). Using rule-based model.",
            "samples": len(X)
        }))
        return
    
    forest = RandomForestClassifier(n_trees=15, max_depth=6, min_samples=2)
    forest.fit(X, y)
    
    correct = 0
    for i in range(len(X)):
        pred = forest.predict(X[i])
        if pred == y[i]:
            correct += 1
    accuracy = round(correct / len(X) * 100, 2)
    
    model_data = {
        "type": "random_forest",
        "trained": True,
        "accuracy": accuracy,
        "total_samples": len(X),
        "feature_names": header[1:14],
        "model": forest.to_dict()
    }
    
    with open(model_path, 'w') as f:
        json.dump(model_data, f, indent=2)
    
    print(json.dumps({
        "success": True,
        "message": f"Model trained successfully on {len(X)} samples with {accuracy}% accuracy.",
        "samples": len(X),
        "accuracy": accuracy
    }))

if __name__ == "__main__":
    train_model()

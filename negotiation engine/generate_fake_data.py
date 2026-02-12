
import csv
import random
import os

random.seed(42)

HEADER = [
    'session_id',
    'offer_percentage',
    'customer_total_orders',
    'customer_total_spent',
    'product_stock',
    'product_days_listed',
    'product_total_sales',
    'active_negotiations',
    'customer_success_rate',
    'customer_avg_discount',
    'round_number',
    'category_id',
    'original_price',
    'min_price_percentage',
    'decision'
]

def decide(offer_pct, cust_orders, cust_spent, stock, days_listed, total_sales, 
           active_neg, success_rate, avg_discount, round_num, category_id, 
           original_price, min_price_pct):
 
    threshold = 88.0

    if cust_orders > 10:
        threshold -= 6
    elif cust_orders > 5:
        threshold -= 4
    elif cust_orders > 2:
        threshold -= 2

    if cust_spent > 30000:
        threshold -= 3
    elif cust_spent > 10000:
        threshold -= 1.5

    if stock < 3:
        threshold += 5
    elif stock < 10:
        threshold += 2

    if days_listed > 60:
        threshold -= 5
    elif days_listed > 30:
        threshold -= 3
    elif days_listed > 14:
        threshold -= 1

    if total_sales > 20:
        threshold += 2
    if active_neg > 3:
        threshold += 2

    if round_num >= 4:
        threshold -= 4
    elif round_num >= 3:
        threshold -= 2
    elif round_num >= 2:
        threshold -= 1

    if success_rate > 0.7:
        threshold -= 2
    if avg_discount > 0 and avg_discount < 10:
        threshold -= 1

    if category_id == 1:
        threshold += 2
    elif category_id == 2:
        threshold -= 2
    elif category_id == 5:
        threshold -= 1

    if offer_pct < min_price_pct:
        return -1

    noise = random.gauss(0, 3)
    effective_offer = offer_pct + noise

    if effective_offer >= threshold:
        return 1
    elif effective_offer >= threshold - 15:
        return 0
    else:
        return -1

def generate_data(num_records=100000):
    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_path = os.path.join(script_dir, "training_data.csv")
    
    with open(output_path, 'w', newline='') as f:
        writer = csv.writer(f)
        writer.writerow(HEADER)
        
        for i in range(1, num_records + 1):
            
            offer_pct = round(random.triangular(50, 100, 85), 2)
            
            cust_orders = int(random.expovariate(0.15))
            if cust_orders > 50:
                cust_orders = 50

            base_spend = cust_orders * random.uniform(500, 3000)
            cust_spent = round(base_spend + random.uniform(0, 2000), 2)

            stock = random.choice(
                [random.randint(1, 5)] * 2 +
                [random.randint(6, 30)] * 5 +
                [random.randint(31, 200)] * 3
            )

            days_listed = random.choice(
                [random.randint(1, 7)] * 3 +
                [random.randint(8, 30)] * 4 +
                [random.randint(31, 180)] * 3
            )

            total_sales = int(random.expovariate(0.1) * (days_listed / 30))
            if total_sales > 100:
                total_sales = 100

            active_neg = random.choices(
                [0, 1, 2, 3, 4, 5, 6, 7, 8],
                weights=[30, 25, 20, 10, 5, 4, 3, 2, 1]
            )[0]

            if cust_orders == 0:
                success_rate = round(random.uniform(0.3, 0.7), 4)
            else:
                success_rate = round(random.betavariate(3, 2), 4)

            avg_discount = round(random.triangular(0, 25, 8), 2)
            if cust_orders == 0:
                avg_discount = 0

            round_num = random.choices(
                [1, 2, 3, 4, 5],
                weights=[40, 25, 18, 10, 7]
            )[0]

            category_id = random.randint(1, 5)

            price_ranges = [
                (100, 500),
                (500, 2000),
                (2000, 10000),
                (10000, 50000),
                (50000, 100000)
            ]
            price_range = random.choice(price_ranges)
            original_price = round(random.uniform(*price_range), 2)

            min_price_pct = round(random.triangular(60, 90, 75), 2)

            decision = decide(
                offer_pct, cust_orders, cust_spent, stock, days_listed,
                total_sales, active_neg, success_rate, avg_discount,
                round_num, category_id, original_price, min_price_pct
            )

            writer.writerow([
                i,
                offer_pct,
                cust_orders,
                cust_spent,
                stock,
                days_listed,
                total_sales,
                active_neg,
                success_rate,
                avg_discount,
                round_num,
                category_id,
                original_price,
                min_price_pct,
                decision
            ])
            
            if i % 10000 == 0:
                print(f"Generated {i:,} / {num_records:,} records...")

    print(f"\nDone! Generated {num_records:,} records.")
    print(f"File: {output_path}")
    
    accept = counter = reject = 0
    with open(output_path, 'r') as f:
        reader = csv.reader(f)
        next(reader)
        for row in reader:
            d = int(row[14])
            if d == 1: accept += 1
            elif d == 0: counter += 1
            else: reject += 1
    
    total = accept + counter + reject
    print(f"\nDistribution:")
    print(f"  Accept:  {accept:,} ({accept/total*100:.1f}%)")
    print(f"  Counter: {counter:,} ({counter/total*100:.1f}%)")
    print(f"  Reject:  {reject:,} ({reject/total*100:.1f}%)")

if __name__ == "__main__":
    generate_data(100000)
